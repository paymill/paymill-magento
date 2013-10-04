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
class Paymill_Paymill_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Is filter allowed
     * 
     * @var boolean
     */
    protected $_isFilterAllowed = true;

    /**
     * Is sortable
     * 
     * @var boolean 
     */
    protected $_isSortable = true;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('log_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Is filter allowed
     */
    protected function _isFilterAllowed()
    {
        return $this->_isFilterAllowed;
    }

    /**
     * Is sortable
     */
    protected function _isSortable()
    {
        return $this->_isSortable;
    }

    /**
     * Retrive massaction block
     *
     * @return Mage_Adminhtml_Block_Widget_Grid_Massaction
     */
    public function getMassactionBlock()
    {
        return $this->getChild('massaction')->setErrorText(Mage::helper('paymill')->__('paymill_error_text_no_entry_selected'));
    }

    /**
     * Prepare Collection
     *
     * @return Paymill_Paymill_Block_Adminhtml_Log_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('paymill/log')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    /**
     * Gets Row Url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }
    
    /**
     * Prepare Columns
     *
     * @return Paymill_Paymill_Block_Adminhtml_Log_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entry_date', array(
            'header' => Mage::helper('paymill')->__('paymill_backend_log_entry_date'),
            'index' => 'entry_date',
        ));
        $this->addColumn('version', array(
            'header' => Mage::helper('paymill')->__('paymill_backend_log_version'),
            'index' => 'version',
        ));
        $this->addColumn('merchant_info', array(
            'header' => Mage::helper('paymill')->__('paymill_backend_log_merchant_info'),
            'index' => 'merchant_info',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepares Massaction for deletion of Logentries
     *
     * @return Paymill_Paymill_Block_Adminhtml_Log_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('log_id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('paymill')->__('paymill_action_delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('paymill')->__('paymill_dialog_confirm'),
        ));

        return $this;
    }

}
