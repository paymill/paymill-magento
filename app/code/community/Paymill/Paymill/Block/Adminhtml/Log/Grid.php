<?php
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

        Mage::helper("paymill/loggingHelper")->log("Logging Grid","Creating Instance");
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
        Mage::helper("paymill/loggingHelper")->log("Logging Grid","Preparing Collection");
        $collection = Mage::getModel('paymill/log')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Columns
     *
     * @return Paymill_Paymill_Block_Adminhtml_Log_Grid
     */
    protected function _prepareColumns()
    {
        Mage::helper("paymill/loggingHelper")->log("Logging Grid","Preparing Columns");
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
        $this->addColumn('dev_info', array(
            'header' => Mage::helper('paymill')->__('paymill_backend_log_dev_info'),
            'index' => 'dev_info',
        ));
        $this->addColumn('dev_info_additional', array(
            'header' => Mage::helper('paymill')->__('paymill_backend_log_dev_info_additional'),
            'index' => 'dev_info_additional',
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
