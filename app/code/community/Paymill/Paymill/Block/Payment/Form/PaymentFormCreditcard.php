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
class Paymill_Paymill_Block_Payment_Form_PaymentFormCreditcard extends Paymill_Paymill_Block_Payment_Form_PaymentFormAbstract
{

    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paymill/payment/form/creditcard.phtml');
    }

    /**
     * Retrieve credit card expire months for Paymill
     *
     * @return array
     */
    public function getPaymillCcMonths()
    {
        $months[0] = $this->__('Month');
        $months = array_merge($months, Mage::getSingleton('payment/config')->getMonths());

        return $months;
    }

    /**
     * Retrieve credit card expire years for Paymill
     *
     * @return array
     */
    public function getPaymillCcYears()
    {
        $years = Mage::getSingleton('payment/config')->getYears();
        $years = array(0 => $this->__('Year')) + $years;

        return $years;
    }
    
    public function getPaymentData($code)
    {
        $payment = parent::getPaymentData($code);
        
        $data = array();
        if (!empty($payment)) {
            $data['cc_number'] = '************' . $payment['last4'];
            $data['expire_year'] = $payment['expire_year'];
            $data['expire_month'] = $payment['expire_month'];
            $data['cvc'] = '***';
            $data['card_holder'] = $payment['card_holder'];
            $data['card_type'] = $payment['card_type'];
        }
        
        return $data;
    }
    
    

}
