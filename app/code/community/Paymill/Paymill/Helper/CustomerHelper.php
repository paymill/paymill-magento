<?php

/**
 * Magento
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)  
 * that is bundled with this package in the file LICENSE.txt.  
 * It is also available through the world-wide-web at this URL:  
 * http://opensource.org/licenses/osl-3.0.php  
 * If you did not receive a copy of the license and are unable to  
 * obtain it through the world-wide-web, please send an email  
 * to license@magentocommerce.com so we can send you a copy immediately.  
 * 
 * @category Paymill  
 * @package Paymill_Paymill  
 * @copyright Copyright (c) 2013 PAYMILL GmbH (https://paymill.com/en-gb/)  
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)  
 */

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
        $custFirstName = $object->getBillingAddress()->getFirstname();
        $custLastName = $object->getBillingAddress()->getLastname();
        $custFullName = $custFirstName . " " . $custLastName;
        return $custFullName;
    }

    /**
     * Returns the current customers email adress.
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $object
     * @return string the customers email adress
     */
    public function getCustomerEmail($object)
    {
        $email = $object->getCustomerEmail();

        if (empty($email)) {
            $email = $object->getBillingAddress()->getEmail();
        }

        return $email;
    }

    /**
     * Return paymill client data
     * @return array
     */
    public function getClientData()
    {
        $clients = new Services_Paymill_Clients(
                Mage::helper('paymill/optionHelper')->getPrivateKey(), Mage::helper('paymill')->getApiUrl()
        );

        $clientId = Mage::helper("paymill/fastCheckoutHelper")->getClientId();

        $client = null;
        if (!empty($clientId)) {
            $client = $clients->getOne($clientId);
            if (!array_key_exists('email', $client)) {
                $client = null;
            }
        }

        return $client;
    }

    /**
     * Returns the Id of the user currently  logged in.
     * Returns null if there is no logged in user.
     * @return String userId
     */
    public function getUserId()
    {
        $result = null;
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $result = Mage::getSingleton('customer/session')->getId();
        }

        return $result;
    }

}