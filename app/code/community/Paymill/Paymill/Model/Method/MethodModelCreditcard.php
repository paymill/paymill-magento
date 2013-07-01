<?php
class Paymill_Paymill_Model_Method_MethodModelCreditcard extends Paymill_Paymill_Model_Method_MethodModelAbstract
{
    /**
     * Magento method code
     *
     * @var string
     */
    protected $_code          = "paymill_creditcard";

    /**
     * Form block identifier
     *
     * @var string
     */
    protected $_formBlockType = 'paymill/payment_form_paymentFormCreditcard';

    /**
     * Info block identifier
     *
     * @var string
     */
    protected $_infoBlockType = 'paymill/payment_info_paymentFormCreditcard';

    /**
     * Deals with payment processing when preAuth mode is active
     */
    public function preAuth(Varien_Object $payment, $amount)
    {
        //Initalizing variables and helpers
        $paymill_flag_client_set    = false;
        $paymill_flag_payment_set   = false;
        $quote                      = $quote = Mage::getSingleton('checkout/session')->getQuote();
        $paymentHelper              = Mage::helper("paymill/paymentHelper");
        $customerHelper             = Mage::helper("paymill/customerHelper");
        $fcHelper                   = Mage::helper("paymill/fastCheckoutHelper");

        //Gathering data
        $token                      = Mage::getSingleton('core/session')->getToken();
        $email                      = $customerHelper->getCustomerEmail($quote);
        $description                = $paymentHelper->getDescription($quote);

        //Loading Fast Checkout Data (if enabled and given)
        if($fcHelper->isFastCheckoutEnabled()){
            $clientId = $fcHelper->getClientId();
            Mage::helper('paymill/loggingHelper')->log("preAuthorization found an existing Client.", $clientId);
            if(isset($clientId)){
                $paymill_flag_client_set = true;
                $paymentId = $fcHelper->getPaymentId($this->_code);
                Mage::helper('paymill/loggingHelper')->log("preAuthorization found an existing Payment.", $paymentId);
                if(isset($paymentId)){
                    $paymill_flag_payment_set = true;
                }
            }
        }

        if(!$paymill_flag_client_set){
            $clientId = $paymentHelper->createClient($email, $description);
        }

        if(!$paymill_flag_payment_set){
            $paymentId = $paymentHelper->createPayment($token, $clientId);
        }

        //Authorize payment
        $transaction = $paymentHelper->createPreAuthorization($paymentId);

        //Save Transaction Data
        $transactionHelper = Mage::helper("paymill/transactionHelper");
        $transactionModel = $transactionHelper->createTransactionModel($transaction['id'], true);
        $transactionHelper->setAdditionalInformation($payment, $transactionModel);
        
        //Save Data for Fast Checkout (if enabled)
        if($fcHelper->isFastCheckoutEnabled()){ //Fast checkout enabled
            if(!$fcHelper->hasData($this->_code)){
                $clientId = $clientId;
                $paymentId = $paymentId;
                $fcHelper->saveData($this->_code, $clientId, $paymentId);
            }
        }
    }

    /**
     * Gets called when a capture gets triggered (default on invoice generation)
     */
    public function capture(Varien_Object $payment, $amount)
    {
        //Initalizing variables and helpers
        $paymentHelper              = Mage::helper("paymill/paymentHelper");
        $transactionHelper          = Mage::helper("paymill/transactionHelper");
        $order                      = $payment->getOrder();

        if($transactionHelper->getPreAuthenticatedFlagState($order)){
            //Capture preAuth
            $preAuthorization = $transactionHelper->getTransactionId($order);
            $captureTransaction = $paymentHelper->createTransactionFromPreAuth($order, $preAuthorization, $amount);

            if (isset($captureTransaction['data']['response_code']) && $captureTransaction['data']['response_code'] !== 20000) {
                $this->_log("An Error occured: " . $captureTransaction['data']['response_code'], var_export($captureTransaction, true));
                throw new Exception("Invalid Result Exception: Invalid ResponseCode");
            }
            Mage::helper('paymill/loggingHelper')->log("Capture created", var_export($captureTransaction, true));

            //Save Transaction Data
            $transactionId = $captureTransaction['id'];
            $transactionModel = $transactionHelper->createTransactionModel($transactionId, true);
            $transactionHelper->setAdditionalInformation($payment, $transactionModel);
        }
    }
}
