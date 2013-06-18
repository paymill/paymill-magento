<?php
/**
 * The Data Helper contains methods dealing with shopiformation.
 * Examples for this might be f.Ex backend option states or pathes.
 */
class Paymill_Paymill_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var int 
     */
    private $_storeId = null;
    
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
        return $this->getGeneralOption("version");
    }


    /**
     * Returns the Public Key from the Backend as a string
     * @return String
     */
    public function getPublicKey()
    {
        return trim($this->getGeneralOption("public_key"));
    }
    
    /**
     * Returns the Private Key from the Backend as a string
     * @return String
     */
    public function getPrivateKey()
    {
        return trim($this->getGeneralOption("private_key"));
    }
    
    /**
     * Returns the state of the "Logging" Switch from the Backend as a Boolean
     * @return Boolean
     */
    public function isLogging()
    {
        return $this->getGeneralOption("logging_active");
    }
    
    /**
     * Returns the state of the "FastCheckout" Switch from the Backend as a Boolean
     * @return Boolean
     */
    public function isSavingFastCheckoutData()
    {
        return $this->getGeneralOption("save_fc_data");
    }
    
    /**
     * Returns the state of the "Debug" Switch from the Backend as a Boolean
     * @return Boolean
     */
    public function isInDebugMode()
    {
        return $this->getGeneralOption("debugging_active");
    }
    
    /**
     * Returns the state of the "Show Labels" Switch from the Backend as a Boolean
     * @return Boolean
     */
    public function isShowingLabels()
    {
        return $this->getGeneralOption("show_label");
    }
    
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
     * Returns the value of the given backend option. 
     * <p align = "center">Needs the $_storeId to be set to work properly</p>
     * @param   String $choice      Name of the desired category as a string
     * @param   String $optionName  Name of the desired option as a string
     * @return  mixed               Value of the Backend Option             
     * @throws  Exception           "No Store Id has been set."
     */
    private function getBackendOption($choice, $optionName)
    {
        if(!(isset($this->_storeId))){
             Mage::throwException("No Store Id has been set.");
        }
        
        try{
            $value = Mage::getStoreConfig('payment/'.$choice.'/'.$optionName, $this->_storeId);
        }catch(Exception $ex){
            $value = null;
        }
        
        return $value;
    }
    
    /**
     * Returns the Value of the general Option with the given name.
     * <p align = "center">Needs the $_storeId to be set to work properly</p>
     * @param String $optionName
     * @return mixed Value
     */
    private function getGeneralOption($optionName)
    {
       return $this->getBackendOption("paymill", $optionName);
    }
        
    /**
     * Sets the store id from f.ex quote, invoice, creditmemo or shipping model
     */
    public function setStoreId()
    {
        $this->_storeId = Mage::app()->getStore()->getStoreId();
    }
}
