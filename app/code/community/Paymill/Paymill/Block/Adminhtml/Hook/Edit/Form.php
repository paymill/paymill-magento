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
class Paymill_Paymill_Block_Adminhtml_Hook_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('paymill')->__('Hook Data')));

        $fieldset->addField('hook_url', 'text', array(
            'name'  => 'hook_url',
            'class' => 'required-entry',
            'label' => Mage::helper('paymill')->__('hook_url'),
            'title' => Mage::helper('paymill')->__('hook_url'),
            'required' => true,
            'value' => Mage::getUrl('paymill/hook/execute', array('_secure' => true))
        ));
        
        $fieldset->addField('hook_types', 'multiselect', array(
            'label'    => Mage::helper('paymill')->__('hook_types'),
            'class'    => 'required-entry',
            'required' => true,
            'name'     => 'hook_types',
            'values'   => Mage::getSingleton('paymill/source_hooks')->toOptionArray(),
            'value' => array('refund.succeeded', 'transaction.succeeded', 'chargeback.executed')
        ));

        $form->setAction($this->getUrl('*/*/save'));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
