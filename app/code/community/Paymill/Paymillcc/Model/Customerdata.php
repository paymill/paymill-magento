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

class Paymill_Paymillcc_Model_Customerdata extends Mage_Core_Model_Abstract
{
    
    /**
     * Construct
     */
    function _construct()
    {
        parent::_construct();
        $this->_init('paymillcc/customerdata');
    }

    public function setEntry($customerId, $data)
    {
        //$this->setId(null);
        $this->setUserId($customerId); 
        $this->setUserData($data);
        $this->save();
        
    }
    
    public function loadByUserId($customerId)
    {
        return $this->loadByAttribute('user_id', $customerId);
    }
    
    public function loadByAttribute($attribute, $value, $additionalAttributes = '*')
    {
        $collection = $this->getResourceCollection()
            ->addFieldToSelect($additionalAttributes)
            ->addFieldToFilter($attribute, $value);

        foreach ($collection as $object) {
            return $object;
        }
        
        return null;
    }

}