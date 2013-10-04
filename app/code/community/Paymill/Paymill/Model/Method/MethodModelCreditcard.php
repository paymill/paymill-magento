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
class Paymill_Paymill_Model_Method_MethodModelCreditcard extends Paymill_Paymill_Model_Method_MethodModelAbstract
{

    /**
     * Magento method code
     *
     * @var string
     */
    protected $_code = "paymill_creditcard";

    /**
     * Form block identifier
     *
     * @var string
     */
    protected $_formBlockType = 'paymill/payment_form_paymentFormCreditcard';

    /**
     * Info block identifier
     *
     * @var string
     */
    protected $_infoBlockType = 'paymill/payment_info_paymentFormCreditcard';

    /**
     * Deals with payment processing when preAuth mode is active
     */
    public function preAuth(Varien_Object $payment, $amount)
    {
        //Gathering data from session
        $token = Mage::getSingleton('core/session')->getToken();

        //Create Payment Processor
        $paymentHelper = Mage::helper("paymill/paymentHelper");
        $fcHelper = Mage::helper("paymill/fastCheckoutHelper");
        $paymentProcessor = $paymentHelper->createPaymentProcessor($this->getCode(), $token);
        $paymentProcessor->setPreAuthAmount(Mage::getSingleton('core/session')->getPreAuthAmount());

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

        //Process Payment
        $success = $paymentProcessor->processPayment(false);

        If ($success) {
            //Save Transaction Data
            $transactionHelper = Mage::helper("paymill/transactionHelper");
            $transactionModel = $transactionHelper->createTransactionModel($paymentProcessor->getPreauthId(), true);
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

        return false;
    }

    /**
     * Gets called when a capture gets triggered (default on invoice generation)
     * 
     * @throws Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        //Initalizing variables and helpers
        $transactionHelper = Mage::helper("paymill/transactionHelper");
        $order = $payment->getOrder();

        if ($transactionHelper->getPreAuthenticatedFlagState($order)) {
            //Capture preAuth
            $preAuthorization = $transactionHelper->getTransactionId($order);
            $privateKey = Mage::helper('paymill/optionHelper')->getPrivateKey();
            $apiUrl = Mage::helper('paymill')->getApiUrl();
            $libBase = null;

            $params = array();
            $params['amount'] = (int) (string) ($amount * 100);
            $params['currency'] = Mage::app()->getStore()->getCurrentCurrencyCode();
            $params['description'] = Mage::helper('paymill/paymentHelper')->getDescription($order);
            $params['source'] = Mage::helper('paymill')->getSourceString();

            $paymentProcessor = new Services_Paymill_PaymentProcessor($privateKey, $apiUrl, $libBase, $params, Mage::helper('paymill/loggingHelper'));
            $paymentProcessor->setPreauthId($preAuthorization);
            
            if (!$paymentProcessor->capture()) {
                Mage::throwException("There was an error processing your capture.");
            }

            Mage::helper('paymill/loggingHelper')->log("Capture created", var_export($paymentProcessor->getLastResponse(), true));

            //Save Transaction Data
            $transactionId = $paymentProcessor->getTransactionId();
            $transactionModel = $transactionHelper->createTransactionModel($transactionId, true);
            $transactionHelper->setAdditionalInformation($payment, $transactionModel);
        }
    }

}
