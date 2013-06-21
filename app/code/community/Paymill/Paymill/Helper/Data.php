<?php
/**
 * The Data Helper contains methods dealing with shopiformation.
 * Examples for this might be f.Ex backend option states or pathes.
 */
class Paymill_Paymill_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Returns the path to the image directory as a string
     * @return string Path
     */
    public function getImagePath()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'skin/frontend/base/default/images/paymill/';
    }
    
    /**
     * Returns the path to the js directory as a string
     * @return string Path
     */
    public function getJscriptPath()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'js/paymill/';
    }
    
    /**
     * Returns the API Url
     * @return string
     */
    public function getApiUrl()
    {
        return "https://api.paymill.com/v2/";
    }
    
    
    /**
     * Returns the version of the plugin as a string
     * @return String Version
     */
    public function getVersion()
    {
        return "v3.0.0";
    }
}
