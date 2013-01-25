<?php

// require this here due to a Magento bug
require_once 'lib/Zend/Log.php';
require_once 'lib/Zend/Log/Formatter/Simple.php';
require_once 'lib/Zend/Log/Writer/Stream.php';

class Paymill_Paymillelv_Model_PaymentMethod extends Mage_Payment_Model_Method_Cc
{

    /**
     * unique internal payment method identifier
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'paymillelv';
    protected $_formBlockType = 'paymillelv/form_paymill';
    protected $_infoBlockType = 'paymillelv/info_paymill';

    /**
     * Is this payment method a gateway (online auth/charge) ?
     */
    protected $_isGateway = true;

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
    protected $_canVoid = false;

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
                "paymill_elv_transaction_token", $data->paymill_elv_transaction_token
        );
        return $this;
    }

    /**
     * Serverside validations.
     */
    public function validate()
    {
        $info = $this->getInfoInstance();
        $token = $info->getAdditionalInformation("paymill_elv_transaction_token");
        if (!$token) {
            self::logAction("No transaction code was received in PaymentMethod (Paymill_Paymillelv_Model_PaymentMethod::validate)");
            Mage::throwException("Error while performing your payment. The payment was not processed.");
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
        $token = $info->getAdditionalInformation("paymill_elv_transaction_token");

        // process the payment
        $result = $this->processPayment($payment, $amount, $token);
        if ($result == false) {
            throw new Exception("Payment was not successfully processed. See log.");
        }

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

        $acceptedCurrencies = Mage::getStoreConfig(
                        'payment/paymillelv/paymill_accepted_currencies', Mage::app()->getStore()
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
            // is active
            $paymillActive = Mage::getStoreConfig(
                            'payment/paymillelv/active', Mage::app()->getStore()
            );

            if (!$paymillActive) {
                return false;
            }

            // get minimum order amount
            $paymillMinimumOrderAmount = Mage::getStoreConfig(
                            'payment/paymillelv/paymill_minimum_order_amount', Mage::app()->getStore()
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

        $libBase = 'lib/paymill/v2/lib/';

        // process the payment
        $result = $this->_processPayment(array(
            'token' => $token,
            'amount' => round($amount * 100),
            'currency' => strtolower($payment->getOrder()->getOrderCurrency()->getCode()),
            'name' => $billing->getName(),
            'email' => $order->getCustomerEmail(),
            'description' => 'Order from: '
            . Mage::getStoreConfig('general/store_information/name', Mage::app()->getStore())
            . ' ' . sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail()),
            'libBase' => $libBase,
            'privateKey' => Mage::getStoreConfig(
                    'payment/paymillelv/paymill_private_api_key', Mage::app()->getStore()
            ),
            'apiUrl' => Mage::getStoreConfig(
                    'payment/paymillelv/paymill_api_endpoint', Mage::app()->getStore()
            ),
            'loggerCallback' => array('Paymill_Paymillcc_Model_PaymentMethod', 'logAction')
                ));

        return $result;
    }

    /**
     * Processes the payment against the paymill API
     * @param $params array The settings array
     * @return boolean
     */
    private function _processPayment($params)
    {

        // setup the logger
        $logger = $params['loggerCallback'];

        // reformat paramters
        $params['currency'] = strtolower($params['currency']);

        // setup client params
        $clientParams = array(
            'email' => $params['email'],
            'description' => $params['name']
        );

        // setup credit card params
        $paymentParams = array(
            'token' => $params['token']
        );

        // setup transaction params
        $transactionParams = array(
            'amount' => $params['amount'],
            'currency' => $params['currency'],
            'description' => $params['description']
        );

        require_once $params['libBase'] . 'Services/Paymill/Transactions.php';
        require_once $params['libBase'] . 'Services/Paymill/Clients.php';
        require_once $params['libBase'] . 'Services/Paymill/Payments.php';

        $clientsObject = new Services_Paymill_Clients(
                        $params['privateKey'], $params['apiUrl']
        );
        $transactionsObject = new Services_Paymill_Transactions(
                        $params['privateKey'], $params['apiUrl']
        );

        $paymentsObject = new Services_Paymill_Payments(
                        $params['privateKey'], $params['apiUrl']
        );

        // perform conection to the Paymill API and trigger the payment
        try {

            // create client
            $client = $clientsObject->create($clientParams);
            if (!isset($client['id'])) {
                call_user_func_array($logger, array("No client created" . var_export($client, true)));
                return false;
            } else {
                call_user_func_array($logger, array("Client created: " . $client['id']));
                call_user_func_array($logger, array("Client: " . var_export($client, true)));
            }

            // create card
            $paymentParams['client'] = $client['id'];
            $payment = $paymentsObject->create($paymentParams);
            if (!isset($payment['id'])) {
                call_user_func_array($logger, array("No payment created: " . var_export($payment, true)));
                return false;
            } else {
                call_user_func_array($logger, array("Payment created: " . $payment['id']));
                call_user_func_array($logger, array("Payment: " . var_export($payment, true)));
            }

            // create transaction        
            $transactionParams['payment'] = $payment['id'];
            $transaction = $transactionsObject->create($transactionParams);
            if (!isset($transaction['id'])) {
                call_user_func_array($logger, array("No transaction created" . var_export($transaction, true)));
                return false;
            } else {
                call_user_func_array($logger, array("Transaction created: " . $transaction['id']));
                call_user_func_array($logger, array("Transaction: " . $transaction));
            }

            // check result
            if (is_array($transaction) && array_key_exists('status', $transaction)) {
                if ($transaction['status'] == "closed") {
                    // transaction was successfully issued
                    return true;
                } elseif ($transaction['status'] == "open") {
                    // transaction was issued but status is open for any reason
                    call_user_func_array($logger, array("Status is open."));
                    return false;
                } else {
                    // another error occured
                    call_user_func_array($logger, array("Unknown error." . var_export($transaction, true)));
                    return false;
                }
            } else {
                // another error occured
                call_user_func_array($logger, array("Transaction could not be issued."));
                return false;
            }
        } catch (Services_Paymill_Exception $ex) {
            // paymill wrapper threw an exception
            call_user_func_array($logger, array("Exception thrown from paymill wrapper: " . $ex->getMessage()));
            return false;
        }

        return true;
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