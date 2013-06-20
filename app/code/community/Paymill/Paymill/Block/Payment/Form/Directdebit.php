<?php
class Paymill_Paymill_Block_Payment_Form_Directdebit extends Paymill_Paymill_Block_Payment_Form_Abstract
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
