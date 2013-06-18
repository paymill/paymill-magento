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
        $dataHelper = Mage::helper("paymill");
        $dataHelper->setStoreId();
        $isLogging = $dataHelper->isLogging();
        
        if($isLogging){
            $this->setId(null)
            ->setEntryDate(null)
            ->setVersion($dataHelper->getVersion())
            ->setMerchantInfo($merchantInfo)
            ->setDevInfo($devInfo)
            ->setDevInfoAdditional($devInfoAdditional)
            ->save();
        }
    }
    
}