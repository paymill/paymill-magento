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

class Paymill_Paymill_Helper_HookHelper extends Mage_Core_Helper_Abstract
{
    /**
     * Webhooks service
     * @var \Services_Paymill_Webhooks
     */
    private $_hooks;
    
    private function _initHooks()
    { 
        $this->_hooks = new Services_Paymill_Webhooks(
            trim(Mage::helper('paymill/optionHelper')->getPrivateKey()), 
            Mage::helper('paymill')->getApiUrl()
        );
        
        return $this;
    }
    
    public function createHook(array $params)
    {
        $this->_initHooks();
        
        $result = $this->_hooks->create($params);
    }
    
    public function getAllHooks()
    {
        $this->_initHooks();
        
        return $this->_hooks->get();
    }
    
    public function deleteHook($id)
    {
        $this->_initHooks();
        
        $this->_hooks->delete($id);
    }
}