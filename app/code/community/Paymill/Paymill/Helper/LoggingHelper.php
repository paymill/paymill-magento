<?php
/**
 * The Logging Helper contains methods dealing with Log entries.
 * Examples for this might be f.Ex logging data or reading from the log.
 */
class Paymill_Paymill_Helper_LoggingHelper extends Mage_Core_Helper_Abstract implements Services_Paymill_LoggingInterface
{
    /**
     * Inserts the arguments into the db log
     * @param String $merchantInfo
     * @param String $devInfo
     * @param String $devInfoAdditional
     */
    public function log($merchantInfo, $devInfo = null, $devInfoAdditional = null)
    {
        Mage::getModel('paymill/log')->log($merchantInfo, $devInfo, $devInfoAdditional);
    }
    
    /**
     * Returns a collection of all log-entries
     * @return Collection Description
     */
    public function getEntries(){
        $collection = Mage::getModel('paymill/log')->getCollection();
        return $collection;
    }
}