<?php
class Paymill_Paymill_Model_Mysql4_Log_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Construct
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('paymill/log');
    }
}