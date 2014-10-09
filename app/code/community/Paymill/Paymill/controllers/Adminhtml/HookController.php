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
class Paymill_Paymill_Adminhtml_HookController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Initialize hooks view
     * 
     * @return Paymill_Paymill_Adminhtml_HookController
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('hooks/paymill_hook');
        return $this;
    }

    /**
     * Action initially called
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }
    
    public function newAction()
    {
        $this->_initAction();
        
        $this->_addContent($this->getLayout()->createBlock('paymill/adminhtml_hook_edit'));
        $this->renderLayout();
    }
    
    public function saveAction()
    {
        $post = $this->getRequest()->getPost();
        if (is_array($post) && array_key_exists('hook_url', $post) && array_key_exists('hook_types', $post)) {
            Mage::helper("paymill/hookHelper")->createHook(array(
                'url' => $post['hook_url'],
                'event_types' => $post['hook_types']
            ));
        }
        
        $this->_redirect('*/*/index');
    }

    /**
     * Normal Magento delete mass action for selected entries
     */
    public function massDeleteAction()
    {
        $hookIds = $this->getRequest()->getParam('hook_id');

        if (!is_array($hookIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('paymill')->__('paymill_error_text_no_entry_selected'));
        } else {
            try {
                foreach ($hookIds as $hookId) {
                    Mage::helper("paymill/hookHelper")->deleteHook($hookId);
                }
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('paymill')->__("paymill_hook_action_success"));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        
        $this->_redirect('*/*/index');
    }

}
