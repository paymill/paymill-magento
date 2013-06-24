<?php
class Paymill_Paymill_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Construct
     */
    public function __construct()
    {
        $this->_blockGroup = 'paymill';
        $this->_controller = 'adminhtml_log';
        $this->_headerText = Mage::helper('paymill')->__('paymill_log');
        parent::__construct();
    }
}