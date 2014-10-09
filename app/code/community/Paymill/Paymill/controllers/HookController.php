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
class Paymill_Paymill_HookController extends Mage_Core_Controller_Front_Action
{    
    public function executeAction()
    {
        $data = json_decode($this->getRequest()->getRawBody(), true);
        if ($data && $this->_validateRequest($data)) {
            $eventResource = $data['event']['event_resource'];
            switch ($data['event']['event_type']) {
                case 'transaction.succeeded':
                    $this->_transactionSucceededAction($eventResource['preauthorization']);
                break;
                case 'refund.succeeded':
                    $this->_refundSucceededAction($eventResource['transaction']);
                break;
                case 'chargeback.executed':
                    $this->_chargebackExecutedAction($eventResource['transaction']);
                break;
            }
        }
    }
    
    private function _transactionSucceededAction(array $data)
    {
        $order = $this->getOrder($data);
        
        if ((int) Mage::helper('paymill/paymentHelper')->getAmount($order) === (int) $data['amount']) {
            Mage::helper('paymill/paymentHelper')->payInvoice($order, $data['id']);
        }
    }
    
    private function _refundSucceededAction(array $data)
    {
        
    }
    
    private function _chargebackExecutedAction(array $data)
    {
        
    }
    
    private function _validateRequest($data)
    {
        $valid = false;
        if (!is_null($data) && isset($data['event']) && isset($data['event']['event_resource'])) {
            
            $transactionId = $data['event']['event_resource']['id'];
            
            if (substr($transactionId, 1, 4) !== 'tran') {
                $transactionId = $data['event']['event_resource']['transaction']['id'];
            }
            
            $transactionObject = new Services_Paymill_Transactions(
                trim(Mage::helper('paymill/optionHelper')->getPrivateKey()), 
                Mage::helper('paymill')->getApiUrl()
            );
            
            $transaction = $transactionObject->getOne($transactionId);

            if (isset($transaction['id']) && ($transaction['id'] === $transactionId)) {
                $valid = true;
            }
        }
        
        return $valid;
    }
    
    private function getOrder(array $data)
    {
        return Mage::getModel('sales/order')->loadByIncrementId(substr($data['description'], 1, 9));
    }
}
