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

/**
 * The Transaction Helper contains methods dealing saving and loading additional transaction data into and from order objects.
 */
class Paymill_Paymill_Helper_TransactionHelper extends Mage_Core_Helper_Abstract
{

    /**
     * Reads the additional data string from the argumented order object and returns it as an instance of Paymill_Paymill_Model_Transaction
     * @param Mage_Sales_Model_Order_Payment $object
     * @return Paymill_Paymill_Model_TransactionData Transaction Model
     */
    public function getAdditionalInformation(Mage_Sales_Model_Order_Payment $object)
    {
        $transactionId = $object->getAdditionalInformation('paymillTransactionId');
        $preAuthflag = $object->getAdditionalInformation('paymillPreAuthFlag');
        $model = $this->createTransactionModel($transactionId, $preAuthflag);
        return $model;
    }

    /**
     * Sets the additional Data string of the argumented object to the valueS of the argumented instance of the Paymill_Paymill_Model_Transaction
     * @param Mage_Sales_Model_Order_Payment $object
     * @param Paymill_Paymill_Model_Transaction $transactionModel Instance of the Transaction Model class
     * @return boolean Indicator of success
     */
    public function setAdditionalInformation(Mage_Sales_Model_Order_Payment $object, Paymill_Paymill_Model_TransactionData $transactionModel)
    {
        $object->setAdditionalInformation('paymillTransactionId', $transactionModel->getTransactionId());
        $object->setAdditionalInformation('paymillPreAuthFlag', $transactionModel->isPreAuthorization());
        $object->setAdditionalInformation('paymillPrenotificationDate', $this->getPrenotificationDate($object->getOrder()));

        Mage::helper('paymill/loggingHelper')->log("Saved Transaction Data.", "Order " . $object->getIncrementId() .
                $object->getReservedOrderId(), var_export($object->getAdditionalInformation(), true));

        return true;
    }

    /**
     * Returns the state of the isPreAuthorization Flag as a boolean
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $object
     * @return booelean PreAuthorizationFlag
     */
    public function isPreAuthenticated($object)
    {
        $payment = $object->getPayment();
        $transactionObject = $this->getAdditionalInformation($payment);
        Mage::helper('paymill/loggingHelper')->log("Read Model from object to return Flag.", var_export($transactionObject, true));
        return $transactionObject->isPreAuthorization();
    }

    /**
     * Returns the transactionId as a string
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $object
     * @return String transactionId
     */
    public function getTransactionId($object)
    {
        $payment = $object->getPayment();
        $transactionObject = $this->getAdditionalInformation($payment);
        Mage::helper('paymill/loggingHelper')->log("Read Model from object to return Transaction Id.", var_export($transactionObject, true));
        return $transactionObject->getTransactionId();
    }

    /**
     * Creates a Transaction Model from the given Data
     * @param String $transactionId
     * @param Boolean $isPreAuthenticated
     * @return Paymill_Paymill_Model_TransactionData Model with the desired attributes
     */
    public function createTransactionModel($transactionId, $isPreAuthenticated = false)
    {
        $transactionModel = new Paymill_Paymill_Model_TransactionData();
        $transactionModel->setTransactionId($transactionId);
        $transactionModel->setPreAuthorizationFlag($isPreAuthenticated);
        return $transactionModel;
    }

    /**
     * Calculates Date with the setted Prenotification Days and formats it
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    private function getPrenotificationDate(Mage_Sales_Model_Order $order)
    {
        $dateTime = new DateTime($order->getCreatedAt());
        $dateTime->modify('+' . Mage::helper('paymill/optionHelper')->getPrenotificationDays() . ' day');
        $date = Mage::app()->getLocale()->storeDate($order->getStore(), Varien_Date::toTimestamp($dateTime->format('Y-m-d H:i:s')), true);
        $date = Mage::helper('core')->formatDate($date, 'short', false);

        return $date;
    }

}