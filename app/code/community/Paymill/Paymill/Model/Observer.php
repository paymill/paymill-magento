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
class Paymill_Paymill_Model_Observer{
   
    /**
     * Registered for the checkout_onepage_controller_success_action event
     * Generates the invoice for the current order
     * 
     * @param Varien_Event_Observer $observer
     */
    public function generateInvoice(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if ($orderIds) {
            $orderId = current($orderIds);
            if (!$orderId) {
                return;
            }
        }
        $order = Mage::getModel('sales/order')->load($orderId);
         
         if($order->getPayment()->getMethod() === 'paymill_creditcard' || $order->getPayment()->getMethod() === 'paymill_directdebit'){
             
            if( Mage::helper('paymill/transactionHelper')->getPreAuthenticatedFlagState($order)){ // If the transaction is not flagged as a debit (not a preAuth) transaction
                Mage::helper('paymill/loggingHelper')->log("Debug", "No Invoice generated, since the transaction is flagged as preauth");
            } else {
                if($order->canInvoice()) {
                    //Create the Invoice
                    Mage::helper('paymill/loggingHelper')->log(Mage::helper('paymill')->__($paymentCode), Mage::helper('paymill')->__('paymill_checkout_generating_invoice'), "Order Id: ".$order->getIncrementId()); 
                    $invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), array());
                    Mage::getModel('sales/order_invoice_api')->capture($invoiceId);
                }
            }
        }
    }
    
    /**
     * Registered for the sales_order_creditmemo_refund event
     * Creates a refund based on the created creditmemo
     * @param Varien_Event_Observer $observer
     */
    public function refundCreditmemo(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();
        if($order->getPayment()->getMethod() === 'paymill_creditcard' || $order->getPayment()->getMethod() === 'paymill_directdebit'){
            $amount = (int)((string)($creditmemo->getGrandTotal()*100));
            Mage::helper('paymill/loggingHelper')->log("Trying to Refund.", var_export($order->getIncrementId(), true), $amount);
            Mage::helper('paymill/refundHelper')->createRefund($order, $amount);
        }
    }
}

