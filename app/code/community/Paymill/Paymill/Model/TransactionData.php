<?php
class Paymill_Paymill_Model_TransactionData
{
    
    private $_preAuthorizationFlag = null;
    private $_transactionId = null;
    
    /**
     * Returns the state of the PreAuthorizationFlag
     * @return Boolean
     */
    public function getPreAuthorizationFlag()
    {
        return $this->_preAuthorizationFlag;
    }
    
    /**
     * Returns the TransactionId as a string
     * @return String
     */
    public function getTransactionId()
    {
        return $this->_transactionId;
    }
    
    /**
     * Sets the PreAuthorizationFlag
     * @param Boolean $flag
     */
    public function setPreAuthorizationFlag($flag)
    {
        $this->_preAuthorizationFlag = $flag;
    }
    
    /**
     * Sets the transaction id
     * @param String $id
     */
    public function setTransactionId($id)
    {
        $this->_transactionId = $id;
    }

}