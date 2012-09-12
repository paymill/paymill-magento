<?php
// require this here due to a Magento bug
require_once 'lib/Zend/Log.php';
require_once 'lib/Zend/Log/Formatter/Simple.php';
require_once 'lib/Zend/Log/Writer/Stream.php';

class Paymill_Paymillcc_Model_PaymentMethod extends Mage_Payment_Model_Method_Cc
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
    protected $_isGateway               = true;
 
    /**
     * Can authorize online?
     */
    protected $_canAuthorize            = true;
 
    /**
     * Can capture funds online?
     */
    protected $_canCapture              = true;
 
    /**
     * Can capture partial amounts online?
     */
    protected $_canCapturePartial       = false;
 
    /**
     * Can refund online?
     */
    protected $_canRefund               = false;
 
    /**
     * Can void transactions online?
     */
    protected $_canVoid                 = true;
 
    /**
     * Can use this payment method in administration panel?
     */
    protected $_canUseInternal          = true;
 
    /**
     * Can show this payment method as an option on checkout payment page?
     */
    protected $_canUseCheckout          = true;
 
    /**
     * Is this payment method suitable for multi-shipping checkout?
     */
    protected $_canUseForMultishipping  = true;
 
    /**
     * Can save credit card information for future processing?
     */
    protected $_canSaveCc = false;
 
    /**
     * Here you will need to implement authorize, capture and void public methods
     *
     * @see examples of transaction specific public methods such as
     * authorize, capture and void in Mage_Paygate_Model_Authorizenet
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
            "paymill_transaction_token", 
            $data->paymill_transaction_token
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
        if (!$token) {
            Mage::throwException("No transaction code was provided.");
        }
        return $this;
    }
    
    /**
     * This method is triggered after order is placed.
     * It is responsible for storing the paymill transaction token
     * into the order and use it later when the invoice is created.
     */ 
    public function authorize(Varien_Object $payment, $amount)
    {

        // get configuration variables
        $paymillCapturePaymentPoint = Mage::getStoreConfig(
            'payment/paymillcc/paymill_capture_point', 
            Mage::app()->getStore()
        );

        $info = $this->getInfoInstance();
         
        // retrieve the transaction_token and save it for later processing
        $token = $info->getAdditionalInformation("paymill_transaction_token");
        
        // save token fpr later processing
        $payment->cc_trans_id = $token;

        if ($paymillCapturePaymentPoint == "order") {
            $this->_capturePayment($payment, $amount);
        }

        return $this;
    }
     
    /**
     * This method triggers the payment.
     * It is triggered when the invoice is created.
     */
    public function capture(Varien_Object $payment, $amount)
    {
        // get configuration variables
        $paymillCapturePaymentPoint = Mage::getStoreConfig(
            'payment/paymillcc/paymill_capture_point', 
            Mage::app()->getStore()
        ); 

        if ($paymillCapturePaymentPoint == "invoice") {
            $this->_capturePayment($payment, $amount);
        }
    }

    /**
     * Specify currency support
     */
    public function canUseForCurrency($currency) {
        if (!in_array($currency, array('EUR'))) {
            return false;
        }
        return true;
    }

    /**
     * Specify minimum order amount from config
     */ 
    public function isAvailable($quote = null) {

        // is active
        $paymillActive = Mage::getStoreConfig(
            'payment/paymillcc/active', 
            Mage::app()->getStore()
        ); 

        if (!$paymillActive) {
            return false;
        }

        // get minimum order amount
        $paymillMinimumOrderAmount = Mage::getStoreConfig(
            'payment/paymillcc/paymill_minimum_order_amount', 
            Mage::app()->getStore()
        ); 

        if($quote && $quote->getBaseGrandTotal() <= 0.5) {
            return false;
        }

        if($quote && $quote->getBaseGrandTotal() <= $paymillMinimumOrderAmount) {
            return false;
        }

        return true;
    }

    /**
     * The actual payment capturing
     */
    public function _capturePayment(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress();


        // get configuration variables
        $paymillPrivateApiKey = Mage::getStoreConfig(
            'payment/paymillcc/paymill_private_api_key', 
            Mage::app()->getStore()
        );
        $paymillApiEndpoint = Mage::getStoreConfig(
            'payment/paymillcc/paymill_api_endpoint', 
            Mage::app()->getStore()
        );

        // get the paymill token (saved in authorized)
        $paymillCreditcardToken = $payment->getCcTransId();

        // get the customer
        $customer = Mage::getModel('customer/session')->getCustomer();

        // setup client params
        $clientParams = array(
            'email' => $order->getCustomerEmail(),
            'description' => $billing->getName()
        );

        // setup credit card params
        $creditcardParams = array(
            'token' => $paymillCreditcardToken
        );

        // setup transaction params
        $transactionParams = array(
            'amount' => $amount * 100,
            'currency' => strtolower($payment->getOrder()->getOrderCurrency()->getCode()),
            'description' => 
                Mage::getStoreConfig('design/head/default_title') 
                . ': ' . sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail())
        );

        // Access objects for the Paymill API
        $clientsObject = new Services_Paymill_Clients(
            $paymillPrivateApiKey, $paymillApiEndpoint
        );
        $creditcardsObject = new Services_Paymill_Creditcards(
            $paymillPrivateApiKey, $paymillApiEndpoint
        );
        $transactionsObject = new Services_Paymill_Transactions(
            $paymillPrivateApiKey, $paymillApiEndpoint
        );

        // perform conection to the Paymill API and trigger the payment
        try {
            // create card
            $creditcard = $creditcardsObject->create($creditcardParams);
            Mage::Log("Creditcard created " . $creditcard['id']);

            // create client
            $clientParams['creditcard'] = $creditcard['id'];
            $client = $clientsObject->create($clientParams);
            Mage::Log("Client created " . $client['id']);

            // create transaction
            $transactionParams['client'] = $client['id'];
            $transaction = $transactionsObject->create($transactionParams);
            Mage::Log("Transaction created " . $transaction['id']);

            if (is_array($transaction) && array_key_exists('status', $transaction)) {
                if ($transaction['status'] == "closed") {
                    return $this;
                } elseif ($transaction['status'] == "open") {
                    Mage::Log('The payment could not be processed. Paymill-Status is open. Please check your Paymill Cockpit to see whether Payment was processed.');
                } else {
                    Mage::Log("The payment could not be processed. Check your Paymill Cockpit for further details.");
                    Mage::throwException(Mage::helper('paymillcc')->__('Your payment was not processed. Your credit card details are invalid.'));
                }
            } else {
                Mage::Log(Mage::helper('paymillcc')->__('Your payment was not processed. Your credit card details are invalid.'));
                Mage::throwException();
            }
        } catch (Services_Paymill_Exception $ex) {
            Mage::Log("An error occured while processing the payment (Paymill): " . $ex->getMessage());
            Mage::throwException(Mage::helper('paymillcc')->__('Your payment was not processed. Please try again later. Error:') . ' ' . $ex->getMessage());
        }  
    }
}
?>