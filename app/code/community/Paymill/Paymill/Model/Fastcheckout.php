<?php
class Paymill_Paymill_Model_Fastcheckout extends Mage_Core_Model_Abstract
{
    
    /**
     * Construct
     */
    function _construct()
    {
        parent::_construct();
        $this->_init('paymill/fastcheckout');
    }
    
    
    /**
     * Returns the clientId matched with the userId passed as an argument.
     * If no match is found, the return value will be null.
     * @param String $userId Unique identifier of the customer
     * @return String clientId matched with the userId <b>can be null if no match is found</b>
     * @todo fill stub
     */
    public function getClientId($userId)
    {
        return null;
    }
    
    /**
     * Returns the paymentId matched with the userId passed as an argument.
     * If no match is found, the return value will be null.
     * @param String $userId Unique identifier of the customer
     * @param String $code PaymentMethodCode
     * @return String paymentId matched with the userId <b>can be null if no match is found</b>
     * @todo fill stub
     */
    public function getPaymentId($userId, $code)
    {
        return null;
    }
    
    /**
     * Saves a set of arguments (paymentMethodCode, clientId and paymentId) as a match to the Id of the current user.
     * The paymentMethodCode is used to bind the Data to the correct payment type.
     * @param String $paymentMethodCode $_code from the payment model
     * @param String $clientId Code returned from the PaymentProcessor used to recreate the current client object
     * @param String $paymentId Code returned from the PaymentProcessor used to recreate the current payment object
     * @return boolean Indicator of Success
     * @todo insert logging instead of logging marks
     */
    public function saveFcData($paymentMethodCode, $clientId, $paymentId)
    {
        //Get UserId
        $userId = $this->getCurrentUserId();
        
        if($userId === null){
            //logging mark
            return false;
        }
        
        //Get Id if it exists
        //logging mark
        $id = $this->getIdByUserId($userId);
        
        //Insert into db
        if($paymentMethodCode === 'paymill_creditcard'){
        //logging mark
        $this->setId($id)
            ->setUserId($userId)
            ->setClientId($clientId)
            ->setCcPaymentId($paymentId)
            ->save();
        }
        if($paymentMethodCode === 'paymill_directdebit'){
        //logging mark
        $this->setId($id)
            ->setUserId($userId)
            ->setClientId($clientId)
            ->setElvPaymentId($paymentId)
            ->save();
        }
        
        return true;
    }
    
    /**
     * Returns the user Id of the customer currently logged in
     * @return String userId
     */
    private function getCurrentUserId()
    {
        return Mage::helper("paymill/customerHelper")->getUserId();
    }
    
    /**
     * Returns the Id of the entry with the userId passed as an argument
     * @param String $userId
     * @return String Key Identifier of a db row. <b>Can be null if nu match is found</b>
     * @todo fill stub
     */
    private function getIdByUserId($userId)
    {
        return null;
    }
}