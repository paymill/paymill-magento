<?php
/**
 * The Refund Helper contains methods dealing with refund processes.
 */
class Paymill_Paymill_Helper_RefundHelper extends Mage_Core_Helper_Abstract
{
    /**
     * Returns the TransactionId of the order with the ordernumber passed as an argument
     * @param String $orderId
     * @return String TransactionId
     */
    private function getTransactionId($orderId)
    {
        throw new Exception("Method not implemented.");
    }
    
    /**
     * Validates the result of the refund
     * @param mixed $refund
     * @return boolean
     */
    private function validateRefund($refund)
    {
        //Logs errorfeedback in case of any other response than ok
        if (isset($refund['data']['response_code']) && $refund['data']['response_code'] !== 20000) {
            Mage::helper('paymill/loggingHelper')->log("An Error occured: " . $refund['data']['response_code'], var_export($refund, true));
            return false;
        }
        
        //Logs feedback in case of an unset id
        if (!isset($refund['id']) && !isset($refund['data']['id'])) {
            Mage::helper('paymill/loggingHelper')->log("No Refund created.", var_export($refund, true));
            return false;
        } else { //Logs success feedback for debugging purposes
            Mage::helper('paymill/loggingHelper')->log("Refund created.", $refund['id']);
        }
        
        return true;
    }
    
    /**
     * Creates a refund from the ordernumber passed as an argument
     * @param String $orderId
     * @return boolean Indicator of success
     */
    public function createRefund($orderId, $amount)
    {
        require_once Mage::getBaseDir('lib') . '/Paymill/v2/lib/Services/Paymill/Refunds.php';
        
        //Gather Data
        try{
        $privateKey                 = Mage::helper('paymill/optionHelper')->getPrivateKey();
        $apiUrl                     = Mage::helper('paymill')->getApiUrl();
        $refundsObject = new Services_Paymill_Refunds( $privateKey, $apiUrl );
        } catch (Exception $ex){
            Mage::helper('paymill/loggingHelper')->log("No Refund created.", $ex->getMessage());
            return false;
        }
        
        //Create Refund
        try{
        $refund = $refundsObject->create(
                array(
                    'transactionId' => $this->getTransactionId($orderId),
                    'params' => array( 'amount' => $amount )
                )
        );
        } catch (Exception $ex){
            Mage::helper('paymill/loggingHelper')->log("No Refund created.", $ex->getMessage(), $orderId);
            return false;
        }
        //Validate Refund and return feedback
        return $this->validateRefund($refund);
    }
}