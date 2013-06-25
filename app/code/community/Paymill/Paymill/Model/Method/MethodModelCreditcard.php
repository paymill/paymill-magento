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
     * Deals with payment processing when preAuth mode is active
     * @todo fill stub
     */
    public function preAuth()
    {
        Mage::throwException("PreAuth not implemented exception");
    }
}
