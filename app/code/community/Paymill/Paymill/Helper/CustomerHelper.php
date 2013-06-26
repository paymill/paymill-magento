<?php
/**
 * The Customer Helper contains methods dealing customer information.
 */
class Paymill_Paymill_Helper_CustomerHelper extends Mage_Core_Helper_Abstract
{
    /**
     * Returns the current customers full name
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $object
     * @return string the customers full name
     */
    public function getCustomerName($object)
    {
        $custFirstName      = $object->getBillingAddress()->getFirstname();
        $custLastName       = $object->getBillingAddress()->getLastname();;
        $custFullName       = $custFirstName . " " . $custLastName;
        return $custFullName;
    }
    
    /**
     * Returns the current customers email adress.
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $object
     * @return string the customers email adress
     */
    public function getCustomerEmail($object)
    {
        return $object->getCustomerEmail();
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