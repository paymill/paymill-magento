<?php
class Paymill_Paymill_Block_Payment_Form_PaymentFormCreditcard extends Paymill_Paymill_Block_Payment_Form_PaymentFormAbstract
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
