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
     * @param String $orderId Unique ordernumber
     * @param String $transactionId Paymill transaction number
     * @param boolean $isPreAuthenticated flag for preAuth transactions
     * @return boolean Indicator of success
     */
    public function saveValueSet($orderId, $transactionId, $isPreAuthenticated = 0)
    {
        //Formatting Arguments in an array for debug-log display reasons
        $arguments = array(
                'orderId' => $orderId,
                'transactionId' => $transactionId,
                'isPreAuthenticated' => $isPreAuthenticated,
            );
        
        //Invoke exception in the handling below if the orderId is an empty string
        $id = null;
        if($orderId === ""){
            $orderId = null;
        }
        
        try{
            $collection = Mage::getModel('paymill/transaction')->getCollection();
            $collection->addFilter('order_id', $orderId);
            $obj = $collection->getFirstItem();
            $objId = $obj->getId();
            
        } catch (Exception $ex){
            Mage::helper('paymill/loggingHelper')->log("Exception ".$ex->getMessage()." caught during getting the orders transaction table index", $orderId);
            $objId = "";
        }
        
        if($objId != ""){
            $id = $objId;
        }
        
        try{
            $this->setId($id)
                    ->setOrderId($orderId)
                        ->setTransactionId($transactionId)
                            ->setIsPreAuthenticated($isPreAuthenticated)
                                ->save();
            
            Mage::helper('paymill/loggingHelper')->log("Transaction Data saved.", print_r($arguments, true));
            return true;
            
        } catch (Exception $ex){
            Mage::helper('paymill/loggingHelper')->log("Transaction Data not saved.", $ex->getMessage(), print_r($arguments, true));
            return false;
        }  
    }
}