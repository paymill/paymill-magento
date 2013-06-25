<?php
class Paymill_Paymill_Model_Method_MethodModelDirectdebit extends Paymill_Paymill_Model_Method_MethodModelAbstract
{
    /**
     * Magento method code
     *
     * @var string
     */
    protected $_code = "paymill_directdebit";
    
    /**
     * Form block identifier
     * 
     * @var string 
     */
    protected $_formBlockType = 'paymill/payment_form_paymentFormDirectdebit';
}
