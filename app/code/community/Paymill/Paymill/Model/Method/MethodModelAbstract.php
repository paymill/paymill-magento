<?php

/**
 * Magento
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)  
 * that is bundled with this package in the file LICENSE.txt.  
 * It is also available through the world-wide-web at this URL:  
 * http://opensource.org/licenses/osl-3.0.php  
 * If you did not receive a copy of the license and are unable to  
 * obtain it through the world-wide-web, please send an email  
 * to license@magentocommerce.com so we can send you a copy immediately.  
 * 
 * @category Paymill  
 * @package Paymill_Paymill  
 * @copyright Copyright (c) 2013 PAYMILL GmbH (https://paymill.com/en-gb/)  
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)  
 */
abstract class Paymill_Paymill_Model_Method_MethodModelAbstract extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Can use the Authorize method
     *
     * @var boolean
     */
    protected $_canAuthorize = true;

    /**
     * Can use the Refund method
     *
     * @var boolean
     */
    protected $_canRefund = true;

    /**
     * Can use the Refund method to refund less than the full amount
     *
     * @var boolean
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Can use the Capture method
     *
     * @var boolean
     */
    protected $_canCapture = true;
    
    /**
     * Can use the partial capture method
     *
     * @var boolean
     */
    protected $_canCapturePartial = false;

    /**
     * Can this method use for checkout
     *
     * @var boolean
     */
    protected $_canUseCheckout = true;

    /**
     * Can this method use for multishipping
     *
     * @var boolean
     */
    protected $_canUseForMultishipping = false;
    

    /**
     * Payment Title
     *
     * @var type
     */
    protected $_methodTitle = '';

    /**
     * Magento method code
     *
     * @var string
     */
    protected $_code = 'paymill_abstract';
    
    /**
     * Paymill error code
     * 
     * @var string
     */
    protected $_errorCode;
    
    /**
     * Is pre-auth
     * 
     * @var boolean
     */
    protected $_preAuthFlag;
    
    /**
     * Can use for internal payments
     * 
     * @var boolean
     */
    protected $_canUseInternal = false;

    /**
     * Check if currency is avaible for this payment
     *
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        $availableCurrencies = explode(',', $this->getConfigData('currency', Mage::app()->getStore()->getId()));
        if (!in_array($currencyCode, $availableCurrencies)) {
            return false;
        }
        return true;
    }

    /**
     * Defines if the payment method is available for checkout
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function isAvailable($quote = null)
    {
        $keysAreSet = Mage::helper("paymill")->isPublicKeySet() && Mage::helper("paymill")->isPrivateKeySet();
        return parent::isAvailable($quote) && $keysAreSet;
    }

    /**
     * Return Quote or Order Object depending on the type of the payment info
     *
     * @return Mage_Sales_Model_Order | Mage_Sales_Model_Order_Quote
     */
    public function getOrder()
    {
        $paymentInfo = $this->getInfoInstance();

        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            return $paymentInfo->getOrder();
        }

        return $paymentInfo->getQuote();
    }

    /**
     * Get the title of every payment option with payment fee if available
     *
     * @return string
     */
    public function getTitle()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $storeId = $quote ? $quote->getStoreId() : null;

        return $this->_getHelper()->__($this->getConfigData('title', $storeId));
    }

    /**
     * Assing data to information model object for fast checkout
     * Saves Session Variables.
     * @param mixed $data
     */
    public function assignData($data)
    {
        parent::assignData($data);
        if (is_array($data)) {
            $post = $data;
        } else {
            $post = $data->getData();
        }
        
        if (array_key_exists('paymill-payment-token-' . $this->_getShortCode(), $post) 
                && !empty($post['paymill-payment-token-' . $this->_getShortCode()])) {
            //Save Data into session
            Mage::getSingleton('core/session')->setToken($post['paymill-payment-token-' . $this->_getShortCode()]);
            Mage::getSingleton('core/session')->setPaymentCode($this->getCode());
        } else {
            if (Mage::helper('paymill/fastCheckoutHelper')->hasData($this->_code)) {
                Mage::getSingleton('core/session')->setToken('dummyToken');
            }
        }

        //Finish as usual
        return $this;
    }

    /**
     * Gets Excecuted when the checkout button is pressed.
     * @param Varien_Object $payment
     * @param float $amount
     * @throws Exception
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $token = Mage::getSingleton('core/session')->getToken();
        if (empty($token)) {
            Mage::helper('paymill/loggingHelper')->log("No token found.");
            Mage::throwException("There was an error processing your payment.");
        }

        if (Mage::helper('paymill/optionHelper')->isPreAuthorizing() && $this->_code === "paymill_creditcard") {
            Mage::helper('paymill/loggingHelper')->log("Starting payment process as preAuth");
            $this->_preAuthFlag = true;
        } else {
            Mage::helper('paymill/loggingHelper')->log("Starting payment process as debit");
            $this->_preAuthFlag = false;
            
        }
        
        $success = $this->payment($payment, $amount);

        if (!$success) {
            Mage::helper('paymill/loggingHelper')->log(Mage::helper("paymill/paymentHelper")->getErrorMessage($this->_errorCode));
            Mage::getSingleton('checkout/session')->setGotoSection('payment');
            Mage::throwException(Mage::helper("paymill/paymentHelper")->getErrorMessage($this->_errorCode));
        }
        
        //Finish as usual
        return parent::authorize($payment, $amount);
    }

    /**
     * Deals with payment processing when debit mode is active
     * @return booelan Indicator of success
     */
    public function payment(Varien_Object $payment, $amount)
    {
        //Gathering data from session
        $token = Mage::getSingleton('core/session')->getToken();
        //Create Payment Processor
        $paymentHelper = Mage::helper("paymill/paymentHelper");
        $fcHelper = Mage::helper("paymill/fastCheckoutHelper");
        $paymentProcessor = $paymentHelper->createPaymentProcessor($this->getCode(), $token);
        
        //Always load client if email doesn't change
        $clientId = $fcHelper->getClientId();
        if (isset($clientId) && !is_null(Mage::helper("paymill/customerHelper")->getClientData())) {
            $paymentProcessor->setClientId($clientId);
        }
        
        //Loading Fast Checkout Data (if enabled and given)
        if ($fcHelper->hasData($this->_code) && $token === 'dummyToken') {
            $paymentId = $fcHelper->getPaymentId($this->_code);
            if (isset($paymentId) && !is_null($fcHelper->getPaymentData($this->_code))) {
                $paymentProcessor->setPaymentId($paymentId);
            }
        }
        
        $success = $paymentProcessor->processPayment(!$this->_preAuthFlag);

        $this->_existingClientHandling($clientId);
        
        if ($success) {
            //Save Transaction Data
            $transactionHelper = Mage::helper("paymill/transactionHelper");
            
            $id = $paymentProcessor->getTransactionId();
            if ($this->_preAuthFlag) {
                $id = $paymentProcessor->getPreauthId();
            }
            
            $transactionModel = $transactionHelper->createTransactionModel($id, $this->_preAuthFlag);
            $transactionHelper->setAdditionalInformation($payment, $transactionModel);
            
            //Allways update the client
            $clientId = $paymentProcessor->getClientId();
            $fcHelper->saveData($this->_code, $clientId);
            
            //Save payment data for FastCheckout (if enabled)
            if ($fcHelper->isFastCheckoutEnabled()) { //Fast checkout enabled
                $paymentId = $paymentProcessor->getPaymentId();
                $fcHelper->saveData($this->_code, $clientId, $paymentId);
            }

            return true;
        }
        
        $this->_errorCode = $paymentProcessor->getErrorCode();

        return false;
    }
    
    /**
     * Handle paymill client update if exist
     * 
     * @param string $clientId
     */
    private function _existingClientHandling($clientId)
    {
        if (!empty($clientId)) {
            $clients = new Services_Paymill_Clients(
                trim(Mage::helper('paymill/optionHelper')->getPrivateKey()),
                Mage::helper('paymill')->getApiUrl()
            );
     
            $quote = Mage::getSingleton('checkout/session')->getQuote();
            
            $client = $clients->getOne($clientId);
            if (Mage::helper("paymill/customerHelper")->getCustomerEmail($quote) !== $client['email']) {
                $clients->update(
                    array(
                        'id' => $clientId,
                        'email' => Mage::helper("paymill/customerHelper")->getCustomerEmail($quote)
                    )
                );
            }
        }
    }
    
    /**
     * Return paymill short code
     * @return string
     */
    protected function _getShortCode()
    {
        $methods = array(
            'paymill_creditcard'  => 'cc',
            'paymill_directdebit' => 'elv'
        );
        
        return $methods[$this->_code];
    }

    /**
     * Handle online refunds and trigger the refund at paymill side
     * 
     * @param Varien_Object $payment
     * @param float $amount
     * @return Paymill_Paymill_Model_Method_MethodModelAbstract
     */
    public function refund(Varien_Object $payment, $amount)
    {
        parent::refund($payment, $amount);
        $order = $payment->getOrder();
        if ($order->getPayment()->getMethod() === 'paymill_creditcard' || $order->getPayment()->getMethod() === 'paymill_directdebit') {
            $amount = (int) ((string) ($amount * 100));
            Mage::helper('paymill/loggingHelper')->log("Trying to Refund.", var_export($order->getIncrementId(), true), $amount);
            
            if (!Mage::helper('paymill/refundHelper')->createRefund($order, $amount)) {
                Mage::throwException('Refund failed.');
            }
        }
        return $this;
    }
    
    /**
     * Set invoice transaction id
     * 
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param type $payment
     */
    public function processInvoice($invoice, $payment)
    {
        parent::processInvoice($invoice, $payment);
        $invoice->setTransactionId(Mage::helper('paymill/transactionHelper')->getTransactionId($payment->getOrder()));
    }
}