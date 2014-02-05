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