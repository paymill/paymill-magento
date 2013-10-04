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
class Paymill_Paymill_Adminhtml_LogController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Initialize logs view
     * 
     * @return Paymill_Paymill_Adminhtml_LogController
     */
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('log/paymill_log');
        return $this;
    }

    /**
     * Action initially called
     */
    public function indexAction()
    {
        // Let's call our initAction method which will set some basic params for each action
        $this->_initAction()
                ->renderLayout();
    }
    
    /**
     * View single xml request or response
     */
    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('paymill/log')->load($id);
        if ($model->getId()) {
            Mage::register('paymill_log_entry', $model);
            $this->_initAction();
            $this->_addContent($this->getLayout()->createBlock('paymill/adminhtml_log_view'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ratepay')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }
    
    /**
     * Normal Magento delete mass action for selected entries
     */
    public function massDeleteAction()
    {
        $logIds = $this->getRequest()->getParam('log_id');

        if (!is_array($logIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('paymill')->__('paymill_error_text_no_entry_selected'));
        } else {
            try {
                foreach ($logIds as $logId) {
                    Mage::getModel('paymill/log')->load($logId)->delete();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('paymill')->__("paymill_log_action_success"));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}
