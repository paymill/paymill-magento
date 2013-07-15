<?php 
/**
 * Magento
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)  
 * that is bundled with this package in the file LICENSE.txt.  
 * It is also available through the world-wide-web at this URL:  
 * http://opensource.org/licenses/osl-3.0.php  
 * If you did not receive a copy of the license and are unable to  
 * obtain it through the world-wide-web, please send an email  
 * to license@magentocommerce.com so we can send you a copy immediately.  
 * 
 * @category Paymill  
 * @package Paymill_Paymill  
 * @copyright Copyright (c) 2013 PAYMILL GmbH (https://paymill.com/en-gb/)  
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)  
 */
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
        return trim($this->_getGeneralOption("public_key"));
    }
    
    /**
     * Returns the Private Key from the Backend as a string
     * @return String
     */
    public function getPrivateKey()
    {
        return trim($this->_getGeneralOption("private_key"));
    }
    
    /**
     * Returns the state of the "Logging" Switch from the Backend as a Boolean
     * @return Boolean
     */
    public function isLogging()
    {
        return $this->_getGeneralOption("logging_active");
    }
    
    /**
     * Returns the state of the "FastCheckout" Switch from the Backend as a Boolean
     * @return Boolean
     */
    public function isFastCheckoutEnabled()
    {
        return $this->_getGeneralOption("fc_active");
    }
    
    /**
     * Returns the state of the "Debug" Switch from the Backend as a Boolean
     * @return Boolean
     */
    public function isInDebugMode()
    {
        return $this->_getGeneralOption("debugging_active");
    }
    
    /**
     * Returns the state of the "Show Labels" Switch from the Backend as a Boolean
     * @return Boolean
     */
    public function isShowingLabels()
    {
        return $this->_getGeneralOption("show_label");
    }
    
    /**
     * Returns the value of the given backend option. 
     * <p align = "center">Needs the $_storeId to be set to work properly</p>
     * @param   String $choice      Name of the desired category as a string
     * @param   String $optionName  Name of the desired option as a string
     * @return  mixed               Value of the Backend Option             
     * @throws  Exception           "No Store Id has been set."
     */
    private function _getBackendOption($choice, $optionName)
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
    private function _getGeneralOption($optionName)
    {
       return $this->_getBackendOption("paymill", $optionName);
    }
    
    /**
     * Returns the state of the "preAuth" Switch from the Backend as a Boolean
     * @return boolean
     */
    public function isPreAuthorizing()
    {
        return $this->_getGeneralOption("preAuth_active");
    }
    
    /**
     * Returns the Token Tolerance Value for the given payment
     * @param String $paymentType Paymentcode
     * @return int Token Tolerance Value (in multiplied format eg 10.00 to 1000)
     */
    public function getTokenTolerance($paymentType)
    {
        $optionValue = $this->_getBackendOption($paymentType, 'tokenTolerance');
        $formattedValue = str_replace ( ',' , '.' , $optionValue );
        $value = (string)(number_format((float)$formattedValue, 2)*100);
        
        return $value;
    }
}
