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
abstract class Paymill_Paymill_Model_Method_MethodModelAbstract extends Mage_Payment_Model_Method_Abstract
{

    /**
     * Is method a gateaway
     *
     * @var boolean
     */
    protected $_isGateway = false;

    /**
     * Can use the Authorize method
     *
     * @var boolean
     */
    protected $_canAuthorize = true;

    /**
     * Can use the Refund method
     *
     * @var boolean
     */
    protected $_canRefund = true;

    /**
     * Can use the Refund method to refund less than the full amount
     *
     * @var boolean
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Can use the Capture method
     *
     * @var boolean
     */
    protected $_canCapture = true;

    /**
     * Can this method use for checkout
     *
     * @var boolean
     */
    protected $_canUseCheckout = true;

    /**
     * Can this method use for multishipping
     *
     * @var boolean
     */
    protected $_canUseForMultishipping = false;

    /**
     * Is a initalize needed
     *
     * @var boolean
     */
    protected $_isInitializeNeeded = false;

    /**
     * Payment Title
     *
     * @var type
     */
    protected $_methodTitle = '';

    /**
     * Magento method code
     *
     * @var string
     */
    protected $_code = 'paymill_abstract';
    
    protected $_errorCode;

    /**
     * Check if currency is avaible for this payment
     *
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        $availableCurrencies = explode(',', $this->getConfigData('currency', Mage::app()->getStore()->getId()));
        if (!in_array($currencyCode, $availableCurrencies)) {
            return false;
        }
        return true;
    }

    /**
     * Defines if the payment method is available for checkout
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function isAvailable($quote = null)
    {
        $keysAreSet = Mage::helper("paymill")->isPublicKeySet() && Mage::helper("paymill")->isPrivateKeySet();
        return parent::isAvailable($quote) && $keysAreSet;
    }

    /**
     * Return Quote or Order Object depending on the type of the payment info
     *
     * @return Mage_Sales_Model_Order | Mage_Sales_Model_Order_Quote
     */
    public function getOrder()
    {
        $paymentInfo = $this->getInfoInstance();

        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            return $paymentInfo->getOrder();
        }

        return $paymentInfo->getQuote();
    }

    /**
     * Get the title of every payment option with payment fee if available
     *
     * @return string
     */
    public function getTitle()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $storeId = $quote ? $quote->getStoreId() : null;

        return $this->_getHelper()->__($this->getConfigData('title', $storeId));
    }

    /**
     * Assing data to information model object for fast checkout
     * Saves Session Variables.
     * @param mixed $data
     */
    public function assignData($data)
    {
        $post = $data->getData();
        if (array_key_exists('paymill-payment-token-' . $this->_getShortCode(), $post) 
                && !empty($post['paymill-payment-token-' . $this->_getShortCode()])) {
            //Save Data into session
            Mage::getSingleton('core/session')->setToken($post['paymill-payment-token-' . $this->_getShortCode()]);
            Mage::getSingleton('core/session')->setPaymentCode($this->getCode());
        } else {
            if (Mage::helper('paymill/fastCheckoutHelper')->hasData($this->_code)) {
                Mage::getSingleton('core/session')->setToken('dummyToken');
            } else {
                Mage::helper('paymill/loggingHelper')->log("No token found.");
                Mage::throwException("There was an error processing your payment.");
            }
        }

        //Finish as usual
        return parent::assignData($data);
    }

    /**
     * Gets Excecuted when the checkout button is pressed.
     * @param Varien_Object $payment
     * @param float $amount
     * @throws Exception
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $success = false;
        if (Mage::helper('paymill/optionHelper')->isPreAuthorizing() && $this->_code === "paymill_creditcard") {
            Mage::helper('paymill/loggingHelper')->log("Starting payment process as preAuth");
            $success = $this->preAuth($payment, $amount);
        } else {
            Mage::helper('paymill/loggingHelper')->log("Starting payment process as debit");
            $success = $this->debit($payment, $amount);
        }

        if (!$success) {
            Mage::helper('paymill/loggingHelper')->log(Mage::helper("paymill/paymentHelper")->getErrorMessage($this->_errorCode));
            Mage::getSingleton('checkout/session')->setGotoSection('payment');
            Mage::throwException(Mage::helper("paymill/paymentHelper")->getErrorMessage($this->_errorCode));
        }
        
        //Finish as usual
        return parent::authorize($payment, $amount);
    }

    /**
     * Deals with payment processing when debit mode is active
     * @return booelan Indicator of success
     */
    public function debit(Varien_Object $payment, $amount)
    {
        //Gathering data from session
        $token = Mage::getSingleton('core/session')->getToken();
        //Create Payment Processor
        $paymentHelper = Mage::helper("paymill/paymentHelper");
        $fcHelper = Mage::helper("paymill/fastCheckoutHelper");
        $paymentProcessor = $paymentHelper->createPaymentProcessor($this->getCode(), $token);
        
        //Always load client if email doesn't change
        $clientId = $fcHelper->getClientId();
        if (isset($clientId)) {
            $paymentProcessor->setClientId($clientId);
        }
        
        //Loading Fast Checkout Data (if enabled and given)
        if ($fcHelper->hasData($this->_code) && $token === 'dummyToken') {
            $paymentId = $fcHelper->getPaymentId($this->_code);
            if (isset($paymentId)) {
                $paymentProcessor->setPaymentId($paymentId);
            }
        }
        
        $success = $paymentProcessor->processPayment();

        If ($success) {
            //Save Transaction Data
            $transactionHelper = Mage::helper("paymill/transactionHelper");
            $transactionModel = $transactionHelper->createTransactionModel($paymentProcessor->getTransactionId(), false);
            $transactionHelper->setAdditionalInformation($payment, $transactionModel);
            
            //Allways update the client
            $clientId = $paymentProcessor->getClientId();
            $fcHelper->saveData($this->_code, $clientId);
            
            //Save payment data for FastCheckout (if enabled)
            if ($fcHelper->isFastCheckoutEnabled()) { //Fast checkout enabled
                $paymentId = $paymentProcessor->getPaymentId();
                $fcHelper->saveData($this->_code, $clientId, $paymentId);
            }

            return true;
        }
        
        $this->_errorCode = $paymentProcessor->getErrorCode();

        return false;
    }
    
    protected function _getShortCode()
    {
        $methods = array(
            'paymill_creditcard'  => 'cc',
            'paymill_directdebit' => 'elv'
        );
        
        return $methods[$this->_code];
    }

}