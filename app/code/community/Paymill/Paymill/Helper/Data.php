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
    
    /**
     * Returns a boolean deciding if the template is going to be displayed of not
     * @param String $code payment code
     * @return boolean
     */
    public function showTemplateForm($code){
        $optionHelper = Mage::helper('paymill/optionHelper');
        $fcHelper = Mage::helper('paymill/fastCheckoutHelper');
        
        return !($optionHelper->isFastCheckoutEnabled() && $fcHelper->hasData($code));
    }
    
    /**
     * Returns the div tag opening defining the visibility of the payment form 
     * @param String $code
     * @return string
     */
    public function getFormTypeForDisplay($code)
    {
        if($this->showTemplateForm($code)){
                return "<div>";
            } else {
                return "<div style='display:none;'>";
            }        
    }
}
