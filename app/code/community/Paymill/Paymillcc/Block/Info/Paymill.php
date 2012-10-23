<?php

class Paymill_Paymillcc_Block_Info_Paymill extends Mage_Payment_Block_Info_Cc
{
    /**
     * Prepare credit card related payment info
     *
     * @param Varien_Object|array $transport
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        return $transport->setData(array());
    }
}
