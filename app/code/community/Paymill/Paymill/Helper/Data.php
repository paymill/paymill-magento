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
        return (string) Mage::getConfig()->getNode()->modules->PayIntelligent_Ratepay->version;
    }

    /**
     * Returns a boolean deciding if the template is going to be displayed of not
     * @param String $code payment code
     * @return boolean
     */
    public function showTemplateForm($code)
    {
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
        if ($this->showTemplateForm($code)) {
            return "<div>";
        } else {
            return "<div style='display:none;'>";
        }
    }

    /**
     * Returns the Source string passt to every transaction
     * @return String Source
     */
    public function getSourceString()
    {
        return $this->getVersion() . "_Magento_" . Mage::getVersion();
    }

}
