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
class Paymill_Paymill_Block_Adminhtml_Log_View_Plane extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Prepare form before rendering HTML
     *
     * @return Paymill_Paymill_Block_Adminhtml_Log_View_Plane
     */
    protected function _prepareForm()
    {
        $this->setTemplate('paymill/log/view.phtml');
        return parent::_prepareForm();
    }

    /**
     * Returns Log Model
     *
     * @return Paymill_Paymill_Model_Log
     */
    public function getEntry()
    {
        return Mage::registry('paymill_log_entry');
    }

    /**
     * Gets the formatted Request Xml
     *
     * @return string
     */
    public function getDevInfo()
    {
        return $this->getEntry()->getDevInfo();
    }

    /**
     * Gets the formatted Response Xml
     *
     * @return string
     */
    public function getDevInfoAdditional()
    {
        return $this->getEntry()->getDevInfoAdditional();
    }

}