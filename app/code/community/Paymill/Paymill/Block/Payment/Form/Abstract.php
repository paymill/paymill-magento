<?php
/**
 * Abstract Form class to specify the basic requirements of any payment objects using the paymill lib
 */
abstract class Paymill_Paymill_Block_Payment_Form_Abstract extends Mage_Payment_Block_Form
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

    /**
     * Returns the state of the One Step Checkout option set in teh backend.
     * @return boolean OneStepCheckoutState
     * @todo Fill stub
     */
    public function getOneStepCheckoutState()
    {
        Mage::throwException("Formclass getOneStepCheckoutState() not implemented.");
    }
    
    /**
     * Returns the Amount for the Javascript to generate the Token from.
     * @return integer Amount without decimals
     * @todo Fill stub
     */
    public function getAmount()
    {
        Mage::throwException("Formclass getAmount() not implemented.");
    }
}
