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
 * The Refund Helper contains methods dealing with refund processes.
 */
class Paymill_Paymill_Helper_RefundHelper extends Mage_Core_Helper_Abstract
{

    /**
     * Validates the result of the refund
     * @param mixed $refund
     * @return boolean
     */
    private function validateRefund($refund)
    {
        //Logs errorfeedback in case of any other response than ok
        if (isset($refund['data']['response_code']) && $refund['data']['response_code'] !== 20000) {
            Mage::helper('paymill/loggingHelper')->log("An Error occured: " . $refund['data']['response_code'], var_export($refund, true));
            return false;
        }

        //Logs feedback in case of an unset id
        if (!isset($refund['id']) && !isset($refund['data']['id'])) {
            Mage::helper('paymill/loggingHelper')->log("No Refund created.", var_export($refund, true));
            return false;
        } else { //Logs success feedback for debugging purposes
            Mage::helper('paymill/loggingHelper')->log("Refund created.", $refund['id'], var_export($refund, true));
        }

        return true;
    }

    /**
     * Creates a refund from the ordernumber passed as an argument
     * @param Mage_Sales_Model_Order_Refund $order
     * @return boolean Indicator of success
     */
    public function createRefund($order)
    {
        //Gather Data
        try {
            $refundsObject = new Services_Paymill_Refunds(
                Mage::helper('paymill/optionHelper')->getPrivateKey(), 
                Mage::helper('paymill')->getApiUrl()
            );
        } catch (Exception $ex) {
            Mage::helper('paymill/loggingHelper')->log("No Refund created due to illegal parameters.", $ex->getMessage());
            return false;
        }

        //Create Refund
        $params = array(
            'transactionId' => $order->getPayment()->getAdditionalInformation('paymillTransactionId'),
            'source' => Mage::helper('paymill')->getSourceString(),
            'params' => array('amount' => (int) ((string) (Mage::helper("paymill/paymentHelper")->getAmount($order) * 100)))
        );
        
        try {
            $refund = $refundsObject->create($params);
        } catch (Exception $ex) {
            Mage::helper('paymill/loggingHelper')->log("No Refund created.", $ex->getMessage(), var_export($params, true));
            return false;
        }
        
        //Validate Refund and return feedback
        return $this->validateRefund($refund);
    }

}