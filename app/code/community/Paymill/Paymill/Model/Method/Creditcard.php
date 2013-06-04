<?php
class Paymill_Paymill_Model_Method_Creditcard extends Paymill_Paymill_Model_Method_Abstract
{
    protected $_code          = "paymill_creditcard";
    protected $_canSaveCc     = false;
    
    /**
     * Form block identifier
     * 
     * @var string 
     */
    protected $_formBlockType = 'paymill/payment_form_creditcard';
}
