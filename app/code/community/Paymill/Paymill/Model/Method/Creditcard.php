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
     * Deals with payment processing when debit mode is active
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
        Mage::throwException("PreAuth not implemented exception");
    }
}
