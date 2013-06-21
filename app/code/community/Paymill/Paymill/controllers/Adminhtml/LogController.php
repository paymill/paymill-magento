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
        Mage::helper("paymill/loggingHelper")->log("Logging Controller","Initializing Log");
        $this->loadLayout()->_setActiveMenu('log/paymill');
        return $this;
    }
    
    /**
     * Render the logs layout
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }
}
