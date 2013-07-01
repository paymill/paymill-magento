<?php
class Paymill_Paymill_Block_Payment_Info_PaymentFormCreditcard extends Mage_Payment_Block_Info
{
    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('paymill/payment/info/creditcard.phtml');
    }

    /**
     * Add custom information to payment method information
     *
     * @param Varien_Object|array $transport
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);

        $data = array();
        $data['paymillTransactionId'] = $this->getInfo()->getAdditionalInformation('paymillTransactionId');
        $data['imgUrl'] = Mage::helper('paymill')->getImagePath() . "icon_paymill.png";

        return $transport->setData(array_merge($data, $transport->getData()));
    }

}
