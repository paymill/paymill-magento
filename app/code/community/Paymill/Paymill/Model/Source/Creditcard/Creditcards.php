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
                'label' => Mage::helper('core')->__('Visa'),
                'value' => 'visa'
            ),
            array(
                'label' => Mage::helper('core')->__('MasterCard'),
                'value' => 'mastercard'
            ),
            array(
                'label' => Mage::helper('core')->__('American Express'),
                'value' => 'amex'
            ),
            array(
                'label' => Mage::helper('core')->__('CartaSi'),
                'value' => 'carta-si'
            ),
            array(
                'label' => Mage::helper('core')->__('Carte Bleue'),
                'value' => 'carte-bleue'
            ),
            array(
                'label' => Mage::helper('core')->__('Diners Club'),
                'value' => 'diners-club'
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
                'label' => Mage::helper('core')->__('China UnionPay'),
                'value' => 'china-unionpay'
            ),
            array(
                'label' => Mage::helper('core')->__('Discover Card'),
                'value' => 'discover'
            ),
            array(
                'label' => Mage::helper('core')->__('Dankort'),
                'value' => 'dankort'
            )
        );
        return $creditcards;
    }
}