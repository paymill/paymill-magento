<?php
class Paymill_Paymill_Model_Source_Creditcard_Creditcards
{
    /**
     * Define which Creditcard Logos are shown for payment
     *
     * @return array
     */
    public function toOptionArray()
    {
        $creditcards = array(
            array(
                'label' => Mage::helper('core')->__('VISA'),
                'value' => 'visa'
            ),
            array(
                'label' => Mage::helper('core')->__('Mastercard'),
                'value' => 'mastercard'
            ),
            array(
                'label' => Mage::helper('core')->__('American Express'),
                'value' => 'amex'
            ),
            array(
                'label' => Mage::helper('core')->__('Carta Si'),
                'value' => 'carta-si'
            ),
            array(
                'label' => Mage::helper('core')->__('Carte Bleue'),
                'value' => 'carte-bleue'
            ),
            array(
                'label' => Mage::helper('core')->__('Dinersclub'),
                'value' => 'dinersclub'
            ),
            array(
                'label' => Mage::helper('core')->__('JCB'),
                'value' => 'jcb'
            ),
            array(
                'label' => Mage::helper('core')->__('Maestro'),
                'value' => 'maestro'
            ),
            array(
                'label' => Mage::helper('core')->__('Unionpay'),
                'value' => 'unionpay'
            )
        );
        return $creditcards;
    }
}