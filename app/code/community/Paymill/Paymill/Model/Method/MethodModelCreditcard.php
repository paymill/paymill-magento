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
    
    public function processInvoice($invoice, $payment)
    {
        $data = $payment->getAdditionalInformation();
        
        if (array_key_exists('paymillPreauthId', $data) && !empty($data['paymillPreauthId'])) {

            $params = array();
            $params['amount'] = (int) Mage::helper("paymill/paymentHelper")->getAmount($invoice);
            $params['currency'] = Mage::helper("paymill/paymentHelper")->getCurrency($invoice);
            $params['description'] = Mage::helper('paymill/paymentHelper')->getDescription($payment->getOrder());
            $params['source'] = Mage::helper('paymill')->getSourceString();

            $paymentProcessor = new Services_Paymill_PaymentProcessor(
                Mage::helper('paymill/optionHelper')->getPrivateKey(), 
                Mage::helper('paymill')->getApiUrl(), 
                null, 
                $params, 
                Mage::helper('paymill/loggingHelper')
            );
            
            $paymentProcessor->setPreauthId($data['paymillPreauthId']);
            
            if (!$paymentProcessor->capture()) {
                Mage::throwException(Mage::helper("paymill/paymentHelper")->getErrorMessage($paymentProcessor->getErrorCode()));
            }

            Mage::helper('paymill/loggingHelper')->log("Capture created", var_export($paymentProcessor->getLastResponse(), true));

            $payment->setAdditionalInformation('paymillTransactionId', $paymentProcessor->getTransactionId());
        }
        
        parent::processInvoice($invoice, $payment);
    }

}
