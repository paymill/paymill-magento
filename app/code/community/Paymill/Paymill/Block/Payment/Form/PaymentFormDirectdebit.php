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
class Paymill_Paymill_Block_Payment_Form_PaymentFormDirectdebit extends Paymill_Paymill_Block_Payment_Form_PaymentFormAbstract
{

    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paymill/payment/form/directdebit.phtml');
    }

    public function getPaymentEntryElv($code)
    {
        $data = $this->getPaymentData($code);
        $fastCheckoutData = array(null,null);
        if(isset($data['iban'])) {
            $fastCheckoutData[0] = $data['iban'];
            $fastCheckoutData[1] = $data['bic'];
        } elseif(isset($data['account'])) {
            $fastCheckoutData[0] = $data['account'];
            $fastCheckoutData[1] = $data['code'];
        }
        return $fastCheckoutData;
    }
}
