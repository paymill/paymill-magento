<?php
/**
 * The Payment Helper contains methods dealing with payment relevant information.
 * Examples for this might be f.Ex customer data, formating of basket amounts or similar.
 */
class Paymill_Paymill_Helper_Payment extends Mage_Core_Helper_Abstract
{
    /**
     * Returns the AuthorizedAmount in the smallest possible unit (f.Ex. cent for the EUR currency).
     * The AuthorizedAmount is the amount the token has been created with.
     * @return int AuthorizedAmount in the smallest avaliable unit
     * @todo fill stub
     */
    public function getAuthorizedAmount()
    {
         Mage::throwException("Paymenthelper getAuthorizedAmount() not implemented");
    }
    
    /**
     * Returns the order amount in the smallest possible unit (f.Ex. cent for the EUR currency)
     * <p align = "center" color = "red">At the moment, only currencies with a 1:100 conversion are supported. Special cases need to be added if necessary</p>
     * @return int Amount in the smallest possible unit
     */
    public function getAmount()
    {
         $decimalTotal = Mage::getSingleton('checkout/session')->getQuote()->getGrandTotal();
         $amountTotal = $decimalTotal * 100;
         return $amountTotal;
    }
    
    /**
     * Returns the currency compliant to ISO 4217 (3 char code)
     * @return string 3 Character long currency code
     */
    public function getCurrency()
    {
         $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
         return $currency_code;
    }
    
    /**
     * Returns the current customers full name
     * @return string the customers full name
     */
    public function getCustomerName()
    {
        $customerAddressId  = Mage::getSingleton('customer/session')->getCustomer()->getBilling(); 
        $address            = Mage::getModel('customer/address')->load($customerAddressId);
        $custFirstName      = $address['firstname'];
        $custLastName       = $address['lastname'];
        $custFullName       = $custFirstName . " " . $custLastName;
        return $custFullName;
    }
    
    /**
     * Returns the current customers email adress.
     * @return string the customers email adress
     * @todo fill stub
     */
    public function getCustomerEmail()
    {
        $customerAddressId = Mage::getSingleton('customer/session')->getCustomer()->getBilling(); 
        $address = Mage::getModel('customer/address')->load($customerAddressId);
        return $address['email'];
    }
    
    /**
     * Returns the description you want to display in the Paymill Backend.
     * The current format is [Shopname] [Email adress of the customer]
     * @return string
     */
    public function getDescription()
    {
        $storename = Mage::app()->getStore()->getName();
        $customerEmail = $this->getCustomerEmail();
        $description = $storename. " " . $customerEmail;
        return $description;
    }
}