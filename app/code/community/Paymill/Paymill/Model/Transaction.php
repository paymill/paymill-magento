<?php
class Paymill_Paymill_Model_Transaction extends Mage_Core_Model_Abstract
{
    
    /**
     * Construct
     */
    function _construct()
    {
        parent::_construct();
        $this->_init('paymill/transaction');
    }
    
    /**
     * Saves a set of data inro the database
     * @param String $userId Unique customer id
     * @param String $orderId Unique ordernumber
     * @param String $transactionId Paymill transaction number
     * @param boolean $isPreAuthenticated flag for preAuth transactions
     * @return boolean Indicator of success
     */
    public function saveValueSet($userId, $orderId, $transactionId, $isPreAuthenticated = 0)
    {
        try{
            $this->setId(null)
                ->setUserId($userId)
                    ->setOrderId($orderId)
                        ->setTransactionId($transactionId)
                            ->setIsPreAuthenticated($isPreAuthenticated)
                                ->save();
            return true;
            
        } catch (Exception $ex){
            $arguments = array(
                'userId' => $userId,
                'orderId' => $orderId,
                'transactionId' => $transactionId,
                'isPreAuthenticated' => $isPreAuthenticated,
            );
            
            Mage::helper('paymill/loggingHelper')->log("Transaction Data not saved.", $ex->getMessage(), print_r($arguments, true));
            return false;
        }  
    }
    
    /**
     * Returns the transactionId of the chosen order (by Id)
     * @param String $orderId Id of the chosen order
     * @return String Desired Transaction Id
     */
    public function getTransaction($orderId)
    {
        $collection = Mage::getModel('paymill/transaction')->getCollection();
        $collection->addFilter('order_id', $orderId);
        $obj = $collection->getFirstItem();
        return $obj->getTransactionId();
    }
    
    /**
     * Returns the state of the isPreAuthenticated Flag for the chosen order
     * @param String $orderId Id of the chosen order
     * @return boolean Flag state
     */
    public function getPreAuthenticatedFlagState($orderId)
    {
        $collection = Mage::getModel('paymill/transaction')->getCollection();
        $collection->addFilter('order_id', $orderId);
        $obj = $collection->getFirstItem();
        $flag = $obj->getIsPreAuthenticated();
        return $flag === 0 ? false : true;
    }
}