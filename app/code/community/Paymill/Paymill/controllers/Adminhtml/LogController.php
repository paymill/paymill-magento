<?php
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
