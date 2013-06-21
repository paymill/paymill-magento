<?php
class Paymill_Paymill_Model_Log extends Mage_Core_Model_Abstract
{
    
    /**
     * Construct
     */
    function _construct()
    {
        parent::_construct();
        $this->_init('paymill/log');
    }
    
    /**
     * Inserts the arguments into the db log
     * @param String $merchantInfo
     * @param String $devInfo
     * @param String $devInfoAdditional
     */
    public function log($merchantInfo, $devInfo, $devInfoAdditional = null)
    {
        if(Mage::helper("paymill/optionHelper")->isLogging()){
            $this->setId(null)
            ->setEntryDate(null)
            ->setVersion(Mage::helper("paymill")->getVersion())
            ->setMerchantInfo($merchantInfo)
            ->setDevInfo($devInfo)
            ->setDevInfoAdditional($devInfoAdditional)
            ->save();
        }
    }
    
}