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
     * Gets called when a capture gets triggered (default on invoice generation)
     * 
     * @throws Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        parent::capture($payment, $amount);
        //Initalizing variables and helpers
        $transactionHelper = Mage::helper("paymill/transactionHelper");
        $order = $payment->getOrder();

        if ($transactionHelper->isPreAuthenticated($order)) {
            //Capture preAuth
            $preAuthorization = $transactionHelper->getTransactionId($order);
            $privateKey = Mage::helper('paymill/optionHelper')->getPrivateKey();
            $apiUrl = Mage::helper('paymill')->getApiUrl();
            $libBase = null;

            $params = array();
            $params['amount'] = (int) (string) ($amount * 100);
            $params['currency'] = $order->getBaseCurrencyCode();
            $params['description'] = Mage::helper('paymill/paymentHelper')->getDescription($order);
            $params['source'] = Mage::helper('paymill')->getSourceString();

            $paymentProcessor = new Services_Paymill_PaymentProcessor($privateKey, $apiUrl, $libBase, $params, Mage::helper('paymill/loggingHelper'));
            $paymentProcessor->setPreauthId($preAuthorization);
            
            if (!$paymentProcessor->capture()) {
                Mage::throwException(Mage::helper("paymill/paymentHelper")->getErrorMessage($paymentProcessor->getErrorCode()));
            }

            Mage::helper('paymill/loggingHelper')->log("Capture created", var_export($paymentProcessor->getLastResponse(), true));

            //Save Transaction Data
            $transactionId = $paymentProcessor->getTransactionId();
            $transactionModel = $transactionHelper->createTransactionModel($transactionId, true);
            $transactionHelper->setAdditionalInformation($payment, $transactionModel);
        }
    }

}
