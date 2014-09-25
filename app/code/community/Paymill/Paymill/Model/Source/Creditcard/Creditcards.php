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