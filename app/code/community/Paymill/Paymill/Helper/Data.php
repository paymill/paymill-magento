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
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'skin/frontend/base/default/images/paymill/';
    }

    /**
     * Returns the path to the js directory as a string
     * @return string Path
     */
    public function getJscriptPath()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'js/paymill/';
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
        return (string) Mage::getConfig()->getNode()->modules->Paymill_Paymill->version;
    }

    /**
     * Returns the Source string passt to every transaction
     * @return String Source
     */
    public function getSourceString()
    {
        return $this->getVersion() . "_Magento_" . Mage::getVersion();
    }

    /**
     * Validates the private key value by comparing it to an empty string
     * @return boolean
     */
    public function isPrivateKeySet()
    {
        return Mage::helper('paymill/OptionHelper')->getPrivateKey() !== "";
    }

    /**
     * Validates the public key value by comparing it to an empty string
     * @return boolean
     */
    public function isPublicKeySet()
    {
        return Mage::helper('paymill/OptionHelper')->getPublicKey() !== "";
    }

}
