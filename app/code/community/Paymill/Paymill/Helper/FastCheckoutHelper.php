<?php
/**
 * The FastCheckout Helper contains methods dealing with the fast checkout process.
 * Examples for this might be f.Ex a customers userId used for matching client data or methods to grant easier access the db information.
 */
class Paymill_Paymill_Helper_FastCheckoutHelper extends Mage_Core_Helper_Abstract
{
    /**
     * Returns the Id of the user currently  logged in.
     * Returns null if there is no logged in user.
     * @return String userId
     */
    public function getUserId()
    {
        $result = null;
        if(Mage::getSingleton('customer/session')->isLoggedIn()){
            $result = Mage::getSingleton('customer/session')->getId();
        }
        return $result;
    }
    
    /**
     * Calls the Data helper to get the state of the Fast Checkout option
     */
    public function isFastCheckoutEnabled()
    {
        return Mage::helper("paymill")->isFastCheckoutEnabled();
    }
    
    
    /**
     * Returns the clientId matched with the current user
     * @return String clientId
     * @todo fill stub
     */
    public function getClientId()
    {
        return null;
    }
    
    /**
     * Returns the PaymentId associated with the current user
     * @param String $code PaymentMethodCode
     * @return String paymentId
     * @todo fill stub
     */
    public function getPaymentId($code)
    {
        return null;
    }
    
    /**
     * Returns a boolean describing whether there is saved fc data for the current user
     * @return boolean
     * @todo fill stub
     */
    public function hasData(){
        return false;
    }
    
}