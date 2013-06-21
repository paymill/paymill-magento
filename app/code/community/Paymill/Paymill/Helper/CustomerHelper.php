<?php
/**
 * The Customer Helper contains methods dealing customer information.
 */
class Paymill_Paymill_Helper_CustomerHelper extends Mage_Core_Helper_Abstract
{
    /**
     * Returns the current customers full name
     * @return string the customers full name
     */
    public function getCustomerName()
    {
        $custFirstName      = Mage::getSingleton('checkout/session')->getQuote()->getCustomerFirstname();
        $custLastName       = Mage::getSingleton('checkout/session')->getQuote()->getCustomerLastname();
        $custFullName       = $custFirstName . " " . $custLastName;
        return $custFullName;
    }
    
    /**
     * Returns the current customers email adress.
     * @return string the customers email adress
     */
    public function getCustomerEmail()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getCustomerEmail();
    }
    
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
}