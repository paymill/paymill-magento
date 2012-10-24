<?php

class Paymill_Paymillcc_Block_Form_Paymill extends Mage_Payment_Block_Form
{
    public $paymillBridgeEndpoint;
    public $paymillPublicApiKey;
    public $paymillDebugMode = "false";

    protected function _construct()
    {
        // read some configuration data

        $this->paymillPublicApiKey = Mage::getStoreConfig(
            'payment/paymillcc/paymill_public_api_key', 
            Mage::app()->getStore()
        );

        $this->paymillBridgeEndpoint = Mage::getStoreConfig(
            'payment/paymillcc/paymill_bridge_endpoint', 
            Mage::app()->getStore()
        );

        $this->paymillDebugMode = Mage::getStoreConfig(
            'payment/paymillcc/paymill_debug_mode', 
            Mage::app()->getStore()
        );
        
        $this->showPaymillLabel = Mage::getStoreConfig(
            'payment/paymillcc/paymill_show_credits', 
            Mage::app()->getStore()
        );
        
        $this->paymillLibVersion = Mage::getStoreConfig(
            'payment/paymillcc/paymill_lib_version', 
            Mage::app()->getStore()
        );
        
        $this->paymillApiEndpoint = Mage::getStoreConfig(
            'payment/paymillcc/paymill_api_endpoint', 
            Mage::app()->getStore()
        );
        
        if ($this->paymillDebugMode == "") {
            $this->paymillDebugMode = "false";
        }

        parent::_construct();

        // load paymill form
        $this->setTemplate('paymill/form/paymill.phtml');
    }
}

?>