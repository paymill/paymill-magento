<?php
abstract class Paymill_Paymill_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract
{
    
    /**
     * Is method a gateaway
     *
     * @var boolean
     */
    protected $_isGateway = false;

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

    /**
     * Return Quote or Order Object depending what the Payment is
     *
     * @return Mage_Sales_Model_Order
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
     * Get the title of every RatePAY payment option with payment fee if available
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
     * Gets Excecuted when the checkout button is pressed.
     * @param Varien_Object $payment
     * @param float $amount
     * @throws Exception
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        if(true){//Debit Mode
            $this->debit();
        } else{ //preAuth Mode
            $this->preAuth();
        }
        
        Mage::throwException("End here for Dev Purpose");
        //Finish as usual
        return parent::authorize($payment, $amount);
    }
    
    /**
     * Deals with payment processing when debit mode is active
     */
    public abstract function debit();
    
    /**
     * Deals with payment processing when preAuth mode is active
     */
    public abstract function preAuth();
}