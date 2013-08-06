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
    public function getEntries()
    {
        $collection = Mage::getModel('paymill/log')->getCollection();
        return $collection;
    }

}