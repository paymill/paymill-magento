<?php
class Paymill_Paymill_Model_Method_MethodModelDirectdebit extends Paymill_Paymill_Model_Method_MethodModelAbstract
{
    protected $_code = "paymill_directdebit";
    
    /**
     * Form block identifier
     * 
     * @var string 
     */
    protected $_formBlockType = 'paymill/payment_form_paymentFormDirectdebit';
       
    
    /**
     * Deals with payment processing when preAuth mode is active
     * @todo fill stub
     */
    public function preAuth()
    {
        Mage::throwException("preAuth not implemented exception");
    }
}
