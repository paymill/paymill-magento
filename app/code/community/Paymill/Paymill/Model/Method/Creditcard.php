<?php
class Paymill_Paymill_Model_Method_Creditcard extends Paymill_Paymill_Model_Method_Abstract
{
    /**
     * Magento method code
     *
     * @var string
     */
    protected $_code          = "paymill_creditcard";
    
    
    protected $_canSaveCc     = false;
    
    /**
     * Form block identifier
     * 
     * @var string 
     */
    protected $_formBlockType = 'paymill/payment_form_creditcard';
       
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
        
        //Save Data for FC
        
        
        //Finish as usual
        return parent::assignData($data);
    }
    
    /**
     * Deals with payment processing when debit mode is active
     */
    public function debit()
    {
        try{
            $token = Mage::getSingleton('core/session')->getToken(); 
            $tokenAmount = Mage::getSingleton('core/session')->getTokenAmount();
            $paymentHelper = Mage::helper("paymill/payment");
            $paymentProcessor = $paymentHelper->createPaymentProcessor($this->getCode(), $token, $tokenAmount);
            $paymentProcessor->processPayment();
        } catch (Exception $ex){
            Mage::throwException($ex->getMessage());
        }
    }
    
    /**
     * Deals with payment processing when preAuth mode is active
     * @todo fill stub
     */
    public function preAuth()
    {
        Mage::throwException("PreAuth not implemented exception");
    }
}
