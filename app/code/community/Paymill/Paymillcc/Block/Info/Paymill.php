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
        $additionalInformation = array();
        if(Mage::app()->getStore()->isAdmin()) {
            $order = Mage::registry('current_order');
            if(!$order && Mage::registry('current_shipment')) {
                $order = Mage::registry('current_shipment')->getOrder();
            }
            elseif (!$order && Mage::registry('current_invoice')) {
                $order = Mage::registry('current_invoice')->getOrder();
            } elseif (!$order && Mage::registry('current_creditmemo')) {
                $order = Mage::registry('current_creditmemo')->getOrder();
            }
            if($order) {
                $additionalInformation = array(
                    'Transaction ID' => ' ' . $order->getPayment()->getAdditionalInformation('paymill_transaction_id')
                );
            }
        }

        return $transport->setData($additionalInformation);
    }
}
