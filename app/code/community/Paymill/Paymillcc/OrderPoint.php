<?php
class Paymill_Paymillcc_OrderPoint
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'order', 'label'=>Mage::helper('adminhtml')->__('Order')),
            array('value' => 'invoice', 'label'=>Mage::helper('adminhtml')->__('Invoice'))
        );
    }
}
?>