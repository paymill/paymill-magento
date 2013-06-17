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
     * Gets Excecuted when the checkout button is pressed.
     * @param type $payment
     * @param type $amount
     * @throws Exception
     * @todo Fill stub
     */
    public function authorize($payment, $amount)
    {
        $token = Mage::getSingleton('core/session')->getToken(); 
        $tokenAmount = Mage::getSingleton('core/session')->getTokenAmount();
        $paymentHelper = Mage::helper("paymill/payment");
        $paymentHelper->createPaymentProcessor($this->getCode(), $token, $tokenAmount);
        
        Mage::throwException("Token $token with amount $tokenAmount.");
    }
}
