<?php
class Paymill_Paymill_Model_Observer{
   
    /**
     * Generates the invoice for the current order
     * 
     * @param Varien_Event_Observer $observer
     */
    public function generateInvoice(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        $paymentCode = Mage::getSingleton('core/session')->getPaymentCode();
        
        if($paymentCode === 'paymill_creditcard' || $paymentCode === 'paymill_directdebit'){
            if ($orderIds) {
                $orderId = current($orderIds);
                if (!$orderId) {
                    return;
                }
                $order = Mage::getModel('sales/order')->load($orderId);
            }

            if($order->canInvoice()) {
                //Create the Invoice
                Mage::helper('paymill/loggingHelper')->log(Mage::helper('paymill')->__($paymentCode), Mage::helper('paymill')->__('paymill_checkout_generating_invoice'), $orderId); 
                $invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), array());
                $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
                $invoice->capture()->save();
                $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING, false)->save();
            }
        }
    }
}

