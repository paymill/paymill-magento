<?php

class Paymill_Paymill_Model_Log_Search extends Varien_Object
{

    /**
     * Load search results
     *
     * @return Paymill_Paymill_Model_Log_Search
     */
    public function load()
    {
        $arr = array();
        $searchText = $this->getQuery();
        $collection = Mage::getModel('paymill/log')->getCollection()
                ->addFieldToFilter(
                    array('dev_info', 'dev_info_additional'), array(
                        array('like' => '%' . $searchText . '%'),
                        array('like' => '%' . $searchText . '%')
                    )
                )
                ->load();

        foreach ($collection as $model) {
            $arr[] = array(
                'id' => 'paymill/search/' . $model->getId(),
                'type' => Mage::helper('adminhtml')->__('Paymill Log Entry'),
                'name' => $model->getMerchantInfo(),
                'description' => $model->getEntryDate(),
                'url' => Mage::helper('adminhtml')->getUrl('paymill/adminhtml_log/view', array('id' => $model->getId())),
            );
        }

        $this->setResults($arr);
        return $this;
    }

}