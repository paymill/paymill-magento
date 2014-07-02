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
 * The Payment Helper contains methods dealing with payment relevant information.
 * Examples for this might be f.Ex customer data, formating of basket amounts or similar.
 */
class Paymill_Paymill_Helper_PaymentHelper extends Mage_Core_Helper_Abstract
{

    /**
     * Error code mapping
     * @var array
     */
    protected $_responseCodes = array(
        '10001' => 'General undefined response.',
        '10002' => 'Still waiting on something.',
        '20000' => 'General success response.',
        '40000' => 'General problem with data.',
        '40001' => 'General problem with payment data.',
        '40100' => 'Problem with credit card data.',
        '40101' => 'Problem with cvv.',
        '40102' => 'Card expired or not yet valid.',
        '40103' => 'Limit exceeded.',
        '40104' => 'Card invalid.',
        '40105' => 'Expiry date not valid.',
        '40106' => 'Credit card brand required.',
        '40200' => 'Problem with bank account data.',
        '40201' => 'Bank account data combination mismatch.',
        '40202' => 'User authentication failed.',
        '40300' => 'Problem with 3d secure data.',
        '40301' => 'Currency / amount mismatch',
        '40400' => 'Problem with input data.',
        '40401' => 'Amount too low or zero.',
        '40402' => 'Usage field too long.',
        '40403' => 'Currency not allowed.',
        '50000' => 'General problem with backend.',
        '50001' => 'Country blacklisted.',
        '50100' => 'Technical error with credit card.',
        '50101' => 'Error limit exceeded.',
        '50102' => 'Card declined by authorization system.',
        '50103' => 'Manipulation or stolen card.',
        '50104' => 'Card restricted.',
        '50105' => 'Invalid card configuration data.',
        '50200' => 'Technical error with bank account.',
        '50201' => 'Card blacklisted.',
        '50300' => 'Technical error with 3D secure.',
        '50400' => 'Decline because of risk issues.',
        '50500' => 'General timeout.',
        '50501' => 'Timeout on side of the acquirer.',
        '50502' => 'Risk management transaction timeout.',
        '50600' => 'Duplicate transaction.'
    );
    
    /**
     * Return message for the given error code
     * 
     * @param string $code
     * @return string
     */
    public function getErrorMessage($code)
    {
        $message = 'General undefined response.';
        if (array_key_exists($code, $this->_responseCodes)) {
            $message = $this->_responseCodes[$code];
        }
        
        return $message;
    }

    /**
     * Returns the order amount in the smallest possible unit (f.Ex. cent for the EUR currency)
     * <p align = "center" color = "red">At the moment, only currencies with a 1:100 conversion are supported. Special cases need to be added if necessary</p>
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $object
     * @return int Amount in the smallest possible unit
     */
    public function getAmount($object = null)
    {
        if ($object == null) {
            $object = Mage::getSingleton('checkout/session')->getQuote();
        }
        $decimalTotal = $object->getBaseGrandTotal();
        $amountTotal = $decimalTotal * 100;
        return round($amountTotal);
    }

    /**
     * Returns the currency compliant to ISO 4217 (3 char code)
     * @return string 3 Character long currency code
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return string
     */
    public function getCurrency($quote)
    {
        return $quote->getBaseCurrencyCode();
    }

    /**
     * Returns the description you want to display in the Paymill Backend.
     * The current format is [OrderId] [Email adress of the customer]
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $object
     * @return string
     */
    public function getDescription($object)
    {
        $orderId = $this->getOrderId($object);
        $customerEmail = Mage::helper("paymill/customerHelper")->getCustomerEmail($object);
        $description = $orderId . ", " . $customerEmail;

        return $description;
    }

    /**
     * Returns the short tag of the Payment
     * @param String $code
     * @return string
     */
    public function getPaymentType($code)
    {
        //Creditcard
        if ($code === "paymill_creditcard") {
            $type = "cc";
        }
        //Directdebit
        if ($code === "paymill_directdebit") {
            $type = "elv";
        }

        return $type;
    }

    /**
     * Returns the reserved order id
     * @param Mage_Sales_Model_Quote|Mage_Sales_Model_Order $object
     * @return String OrderId
     */
    public function getOrderId($object)
    {
        $orderId = null;

        if ($object instanceof Mage_Sales_Model_Order) {
            $orderId = $object->getIncrementId();
        }

        if ($object instanceof Mage_Sales_Model_Quote) {
            $orderId = $object->getReservedOrderId();
        }


        return $orderId;
    }

    /**
     * Returns an instance of the paymentProcessor class.
     * @param String $paymentCode name of the payment
     * @param String $token Token generated by the Javascript
     * @return Services_Paymill_PaymentProcessor
     */
    public function createPaymentProcessor($paymentCode, $token)
    {
        $privateKey = Mage::helper('paymill/optionHelper')->getPrivateKey();
        $apiUrl = Mage::helper('paymill')->getApiUrl();
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $libBase = null;

        $params = array();
        $params['token'] = $token;
        $params['amount'] = (int) $this->getAmount();
        $params['currency'] = $this->getCurrency($quote);
        $params['payment'] = $this->getPaymentType($paymentCode); // The chosen payment (cc | elv)
        $params['name'] = Mage::helper("paymill/customerHelper")->getCustomerName($quote);
        $params['email'] = Mage::helper("paymill/customerHelper")->getCustomerEmail($quote);
        $params['description'] = $this->getDescription($quote);
        $params['source'] = Mage::helper('paymill')->getSourceString();

        return new Services_Paymill_PaymentProcessor($privateKey, $apiUrl, $libBase, $params, Mage::helper('paymill/loggingHelper'));
    }

}
