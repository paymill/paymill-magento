<?php
/**
 * The Option Helper contains methods dealing with reading out backend options.
 */
class Paymill_Paymill_Helper_OptionHelper extends Mage_Core_Helper_Abstract
{
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
    public function isFastCheckoutEnabled()
    {
        return $this->getGeneralOption("fc_active");
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
     * Returns the value of the given backend option. 
     * <p align = "center">Needs the $_storeId to be set to work properly</p>
     * @param   String $choice      Name of the desired category as a string
     * @param   String $optionName  Name of the desired option as a string
     * @return  mixed               Value of the Backend Option             
     * @throws  Exception           "No Store Id has been set."
     * @todo Translations for Exceptions
     */
    private function getBackendOption($choice, $optionName)
    {
        try{
            $value = Mage::getStoreConfig('payment/'.$choice.'/'.$optionName, Mage::app()->getStore()->getStoreId());
        }catch(Exception $ex){
            $value = "An Error has occoured getting the config element";
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
     * Returns the state of the "preAuth" Switch from the Backend as a Boolean
     * @return boolean
     */
    public function isPreAuthorizing()
    {
        return $this->getGeneralOption("preAuth_active");
    }
}
