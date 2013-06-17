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
 * @category PayIntelligent
 * @package PayIntelligent_RatePAY
 * @copyright Copyright (c) 2011 PayIntelligent GmbH (http://www.payintelligent.de)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

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
            ->setVersion($dataHelper->getGeneralOption("version"))
            ->setMerchantInfo($merchantInfo)
            ->setDevInfo($devInfo)
            ->setDevInfoAdditional($devInfoAdditional)
            ->save();
        }
    }
    
}