<?php
class Paymill_Paymill_Block_Payment_Form_Directdebit extends Paymill_Paymill_Block_Payment_Form_Abstract
{
    /**
     * Construct
     */
    protected function _construct()
    {
        Mage::log("ELV Form block");
        parent::_construct();
        $this->setTemplate('paymill/payment/form/directdebit.phtml');
        
    }
}
