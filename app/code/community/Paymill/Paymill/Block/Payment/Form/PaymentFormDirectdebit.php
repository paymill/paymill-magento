<?php
class Paymill_Paymill_Block_Payment_Form_PaymentFormDirectdebit extends Mage_Payment_Block_Form
{
    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paymill/payment/form/directdebit.phtml');
        
    }
}
