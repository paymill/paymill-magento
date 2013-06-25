<?php
class Paymill_Paymill_Model_Mysql4_Transaction extends Mage_Core_Model_Mysql4_Abstract
{
    
    /**
     * Construct
     */
    function _construct()
    {
        $this->_init('paymill/transaction', 'id');
    }
}