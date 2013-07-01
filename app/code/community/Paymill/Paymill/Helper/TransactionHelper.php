<?php

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
    public function getAdditionalInformation($object)
    {
        try
        {
            $transactionId = $object->getAdditionalInformation('paymillTransactionId');
            $preAuthflag = $object->getAdditionalInformation('paymillPreAuthFlag');
            $model = $this->createTransactionModel($transactionId, $preAuthflag);
            Mage::helper('paymill/loggingHelper')->log("Read Model from object.", var_export($model, true));
        } catch (Exception $ex)
        {
            Mage::helper('paymill/loggingHelper')->log("Transaction Helper encountered a problem.", "There was an error during unserialization of the Transaction Object.", $ex->getMessage());
            Mage::throwException("There was an error during unserialization of the Transaction Object. " . $ex->getMessage());
        }

        return $model;
    }

    /**
     * Sets the additional Data string of the argumented object to the serialized value of the argumented instance of the Paymill_Paymill_Model_Transaction
     * @param Mage_Sales_Model_Order_Payment $object
     * @param Paymill_Paymill_Model_Transaction $transactionModel Instance of the Transaction Model class
     * @return boolean Indicator of success
     */
    public function setAdditionalInformation($object, Paymill_Paymill_Model_TransactionData $transactionModel)
    {
        $object->setAdditionalInformation('paymillTransactionId', $transactionModel->getTransactionId());
        $object->setAdditionalInformation('paymillPreAuthFlag', $transactionModel->getPreAuthorizationFlag());
        Mage::helper('paymill/loggingHelper')->log("Saved Transaction Data.", "Order " . $object->getIncrementId() .
                $object->getReservedOrderId(), var_export($object->getAdditionalInformation(), true));

        return true;
    }

    /**
     * Returns the state of the isPreAuthorization Flag as a boolean
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $object
     * @return booelean PreAuthorizationFlag
     */
    public function getPreAuthenticatedFlagState($object)
    {
        $payment = $object->getPayment();
        $transactionObject = $this->getAdditionalInformation($payment);
        return $transactionObject->getPreAuthorizationFlag();
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
        return $transactionObject->getTransactionId();
    }

    /**
     * Creates a Transaction Model from the given Data
     * @param String $transactionId
     * @param Boolean $isPreAuthenticated
     * @return boolean Indicator of success
     */
    public function createTransactionModel($transactionId, $isPreAuthenticated = false)
    {
        $transactionModel = new Paymill_Paymill_Model_TransactionData();
        $transactionModel->setTransactionId($transactionId);
        $transactionModel->setPreAuthorizationFlag($isPreAuthenticated);
        return $transactionModel;
    }

}