<?php
class Paymill_Paymill_Block_Payment_Form_Creditcard extends Paymill_Paymill_Block_Payment_Form_Abstract
{
    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paymill/payment/form/creditcard.phtml');
     }
}
