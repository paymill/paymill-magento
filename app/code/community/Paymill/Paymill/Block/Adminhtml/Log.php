<?php
class Paymill_Paymill_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Construct
     */
    public function __construct()
    {
        Mage::helper("paymill/loggingHelper")->log("Logging Block","Creating Instance");
        $this->_blockGroup = 'Paymill';
        $this->_controller = 'adminhtml_log';
        $this->_headerText = Mage::helper('Paymill')->__('paymill_log');
        parent::__construct();
    }
}