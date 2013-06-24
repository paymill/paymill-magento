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
}
