<?php
class Paymill_Paymill_Model_Method_Directdebit extends Paymill_Paymill_Model_Method_Abstract
{
    protected $_code = "paymill_directdebit";
    
    protected $_canSaveCc     = false;
    
    /**
     * Form block identifier
     * 
     * @var string 
     */
    protected $_formBlockType = 'paymill/payment_form_directdebit';
       
    /**
     * Deals with payment processing when debit mode is active
     * @todo fill stub
     */
    public function debit()
    {
         $token = Mage::getSingleton('core/session')->getToken(); 
        $tokenAmount = Mage::getSingleton('core/session')->getTokenAmount();
        $paymentHelper = Mage::helper("paymill/payment");
        $paymentProcessor = $paymentHelper->createPaymentProcessor($this->getCode(), $token, $tokenAmount);
        $paymentProcessor->processPayment();
        
    }
    
    /**
     * Deals with payment processing when preAuth mode is active
     * @todo fill stub
     */
    public function preAuth()
    {
        Mage::throwException("preAuth not implemented exception");
    }
}
