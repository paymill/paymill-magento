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
         Mage::throwException("debit not implemented exception");
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
