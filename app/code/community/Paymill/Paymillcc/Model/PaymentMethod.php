<?php

// require this here due to a Magento bug
require_once 'lib/Zend/Log.php';
require_once 'lib/Zend/Log/Formatter/Simple.php';
require_once 'lib/Zend/Log/Writer/Stream.php';

class Paymill_Paymillcc_Model_PaymentMethod extends Paymill_Paymillcc_Model_PaymentAbstract
{

    /**
     * unique internal payment method identifier
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'paymillcc';
    protected $_formBlockType = 'paymillcc/form_paymill';
    protected $_infoBlockType = 'paymillcc/info_paymill';

    /**
     * Is this payment method a gateway (online auth/charge) ?
     */
    protected $_isGateway = false;

    /**
     * Can authorize online?
     */
    protected $_canAuthorize = true;

    /**
     * Can capture funds online?
     */
    protected $_canCapture = false;

    /**
     * Can capture partial amounts online?
     */
    protected $_canCapturePartial = false;

    /**
     * Can refund online?
     */
    protected $_canRefund = false;

    /**
     * Can void transactions online?
     */
    protected $_canVoid = true;

    /**
     * Can use this payment method in administration panel?
     */
    protected $_canUseInternal = false;

    /**
     * Can show this payment method as an option on checkout payment page?
     */
    protected $_canUseCheckout = true;

    /**
     * Is this payment method suitable for multi-shipping checkout?
     */
    protected $_canUseForMultishipping = true;

    /**
     * Can save credit card information for future processing?
     */
    protected $_canSaveCc = false;

    /**
     */
    public function assignData($data)
    {

        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();

        // read the paymill_transaction_token from the credit 
        // card form and store it for later use
        $info->setAdditionalInformation(
            "paymill_transaction_token", $data->paymill_transaction_token
        );
        
        return $this;
    }

    /**
     * Serverside validations.
     */
    public function validate()
    {
        $info = $this->getInfoInstance();
        $token = $info->getAdditionalInformation("paymill_transaction_token");
        if (!$token && is_null(Mage::getSingleton("paymillcc/customerdata")->loadByUserId(Mage::getSingleton('customer/session')->getCustomer()->getId()))) {
            self::logAction("No transaction code was received in PaymentMethod (Paymill_Paymillcc_Model_PaymentMethod::validate)");
            Mage::throwException(
                Mage::helper('paymillelv')->__("Error while performing your payment. The payment was not processed.")
            );
        }
        return $this;
    }

    /**
     * This method is triggered after order is placed.
     *
     * @return boolean Returns true if the payment was successfully processed
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $info = $this->getInfoInstance();
        // retrieve the transaction_token and save it for later processing
        $token = $info->getAdditionalInformation("paymill_transaction_token");

        // process the payment
        $result = $this->processPayment($payment, $amount, $token);
        if ($result == false) {
            $payment->setStatus('ERROR')->setIsTransactionClosed(1)->save();
            throw new Exception(
                 Mage::helper('paymillcc')->__("Payment was not successfully processed. See log.")
            );
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn() && Mage::getStoreConfig('payment/paymillcc/recurring', Mage::app()->getStore())) {
            if (is_null(Mage::getSingleton("paymillcc/customerdata")->loadByUserId(Mage::getSingleton('customer/session')->getCustomer()->getId()))) {
                Mage::getSingleton("paymillcc/customerdata")->setEntry(
                    Mage::getSingleton('customer/session')->getCustomer()->getId(),
                    Mage::getSingleton('core/session')->getPaymillCcClientToken() . '|' . Mage::getSingleton('core/session')->getPaymillCcPaymentToken()
                );
            }
        }
        
        $transactionId = Mage::getSingleton('core/session')->getPaymillTransactionId();
        $info->setAdditionalInformation('paymill_transaction_id', $transactionId);
        $payment->setStatus('APPROVED')
                ->setTransactionId($transactionId)
                ->setIsTransactionClosed(1)
                ->save();
        
        
        return $this;
    }

    /**
     * This method triggers the payment.
     * It is triggered when the invoice is created.
     * @return void
     */
    public function capture(Varien_Object $payment, $amount)
    {
        return $this;
    }

    /**
     * Specify currency support
     */
    public function canUseForCurrency($currency)
    {
        $currency = Mage::getSingleton('checkout/session')->getQuote()->getQuoteCurrencyCode();
        Mage::getSingleton('core/session')->setPaymillPaymentCurrency($currency);
        $acceptedCurrencies = Mage::getStoreConfig(
            'payment/paymillcc/paymill_accepted_currencies', Mage::app()->getStore()
        );
        
        $acceptedCurrenciesExploded = explode(',', trim(strtolower($acceptedCurrencies)));

        if (!in_array(strtolower($currency), $acceptedCurrenciesExploded)) {
            return false;
        }

        return true;
    }

    /**
     * Specify minimum order amount from config
     * @return boolean Returns true if the payment method is available for the current context
     */
    public function isAvailable($quote = null)
    {
        if (is_object($quote)) {
            $amount = number_format($quote->getBaseGrandTotal(), 2, '.', '');

            Mage::getSingleton('core/session')->setPaymillPaymentAmount($amount);

            // is active
            $paymillActive = Mage::getStoreConfig(
                            'payment/paymillcc/active', Mage::app()->getStore()
            );

            if (!$paymillActive) {
                return false;
            }

            // get minimum order amount
            $paymillMinimumOrderAmount = Mage::getStoreConfig(
                            'payment/paymillcc/paymill_minimum_order_amount', Mage::app()->getStore()
            );

            if ($quote && $quote->getBaseGrandTotal() <= 0.5) {
                return false;
            }

            if ($quote && $quote->getBaseGrandTotal() <= $paymillMinimumOrderAmount) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * The payment capturing method
     * @param Varien_Object $payment The payment object
     * @param $amount The amount to be captures
     * @return boolean $result Returns true if the payment was successfully processed
     */
    public function processPayment(Varien_Object $payment, $amount, $token)
    {
        // get some relevant objects
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress();

        // check the library version
        $paymillLibraryVersion = Mage::getStoreConfig(
                        'payment/paymillcc/paymill_lib_version', Mage::app()->getStore()
        );

        // keep this for further versions 
        if ($paymillLibraryVersion == "v2") {
            $libBase = 'lib/paymill/v2/lib/';
            $libVersion = 'v2';
        } else {
            $libBase = 'lib/paymill/v2/lib/';
            $libVersion = 'v2';
        }
        
        $data = array(
            'libVersion' => $libVersion,
            'token' => $token,
            'amount' => round($amount * 100),
            'currency' => strtoupper($payment->getOrder()->getOrderCurrency()->getCode()),
            'name' => $billing->getName(),
            'email' => $order->getCustomerEmail(),
            'description' => 'Order from: '
            . Mage::getStoreConfig('general/store_information/name', Mage::app()->getStore())
            . ' ' . sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail()),
            'libBase' => $libBase,
            'privateKey' => Mage::getStoreConfig(
                    'payment/paymillcc/paymill_private_api_key', Mage::app()->getStore()
            ),
            'apiUrl' => Mage::getStoreConfig(
                    'payment/paymillcc/paymill_api_endpoint', Mage::app()->getStore()
            ),
            'loggerCallback' => array('Paymill_Paymillcc_Model_PaymentMethod', 'logAction')
        );
        
        $paymillUser = Mage::getSingleton("paymillcc/customerdata")->loadByUserId(Mage::getSingleton('customer/session')->getCustomer()->getId());
        if (!is_null($paymillUser)){
            $token = explode('|', $paymillUser->getUserData());
            $data['client_id']  = $token[0];
            $data['payment_id'] = $token[1];
        }
        
        // process the payment
        $result = $this->_processPayment($data);
        

        
        return $result;
    }
    
    protected function _setPaymillClientToken($id)
    {
        Mage::getSingleton('core/session')->setPaymillCcClientToken($id);
    }
    
    protected function _setPaymillPaymentToken($id)
    {
        Mage::getSingleton('core/session')->setPaymillCcPaymentToken($id);
    }
    
    protected function _setPaymillTransactionId($id)
    {
        Mage::getSingleton('core/session')->setPaymillTransactionId($id);
    }
    
    /**
     * Logs an event
     * @param $message The message to be logged
     */
    public static function logAction($message)
    {
        Mage::log($message);
    }

}