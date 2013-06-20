<?php
/**
 * Abstract Form class to specify the basic requirements of any payment objects using the paymill lib
 */
abstract class Paymill_Paymill_Block_Payment_Form_PaymentFormAbstract extends Mage_Payment_Block_Form
{
    /**
     * returns the PaymentHelper
     * @return Paymill_Paymill_Helper_Payment PaymentHelper
     */
    public final function getPaymentHelper()
    {
       $paymentHelper = Mage::helper('paymill/payment');
       return $paymentHelper;
    }
    
    /**
     * Returns the DataHelper
     * @return Paymill_Paymill_Helper_Data DataHelper
     */
    public final function getDataHelper()
    {
        $dataHelper = Mage::helper('paymill');
        $dataHelper->setStoreId();
        return $dataHelper;
    }
}
