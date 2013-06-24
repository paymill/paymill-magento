<?php
abstract class Paymill_Paymill_Model_Method_MethodModelAbstract extends Mage_Payment_Model_Method_Abstract
{
    
    /**
     * Is method a gateaway
     *
     * @var boolean
     */
    protected $_isGateway = false;
    
    /**
     * Can use the Authorize method
     * 
     * @var boolean 
     */
    protected $_canAuthorize = true;

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
     * Is a initalize needed
     *
     * @var boolean
     */
    protected $_isInitializeNeeded = false;

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
     * Return Quote or Order Object depending on the type of the payment info
     *
     * @return Mage_Sales_Model_Order
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
        //Recieve Data
        $postData = Mage::app()->getRequest()->getPost();
        $token = $postData['payment']['paymill-payment-token'];
        $tokenAmount = $postData['payment']['paymill-payment-amount'];
        
        //Save Data into session
        Mage::getSingleton('core/session')->setToken($token);
        Mage::getSingleton('core/session')->setTokenAmount($tokenAmount);
        
        //Finish as usual
        return parent::assignData($data);
    }
    
    /**
     * Gets Excecuted when the checkout button is pressed.
     * @param Varien_Object $payment
     * @param float $amount
     * @throws Exception
     * @todo Define terms in which preAuth is chosen over debit
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        if(true){//Debit Mode
            $this->debit();
        } else{ //preAuth Mode
            $this->preAuth();
        }
        
        //Finish as usual
        return parent::authorize($payment, $amount);
    }
    
    /**
     * Deals with payment processing when debit mode is active
     */
    public function debit()
    {
        //Gathering data from session
        $token = Mage::getSingleton('core/session')->getToken(); 
        $tokenAmount = Mage::getSingleton('core/session')->getTokenAmount();
                
        //Create Payment Processor
        $paymentHelper = Mage::helper("paymill/paymentHelper");
        $fcHelper = Mage::helper("paymill/fastCheckoutHelper");
        $paymentProcessor = $paymentHelper->createPaymentProcessor($this->getCode(), $token, $tokenAmount);
        
        //Loading Fast Checkout Data (if enabled and given)
        if($fcHelper->isFastCheckoutEnabled()){
            $clientId = $fcHelper->getClientId();
            if(isset($clientId)){
                $paymentId = $fcHelper->getPaymentId($this->_code);
                if(isset($paymentId)){
                    $paymentProcessor->setClientId($clientId);
                    $paymentProcessor->setPaymentId($paymentId);
                }
            }  
        }
                
        //Process Payment
        $paymentProcessor->processPayment();
        
        //Save Data for Fast Checkout (if enabled)
        if($fcHelper->isFastCheckoutEnabled()){ //Fast checkout enabled
            if(!$fcHelper->hasData($this->_code)){
                $clientId = $paymentProcessor->getClientId();
                $paymentId = $paymentProcessor->getPaymentId();
                $fcHelper->saveData($clientId, $paymentId);
            }
        }
        
    }
    
    /**
     * Deals with payment processing when preAuth mode is active
     */
    public abstract function preAuth();
}