<?php

class Paymill_Paymill_TokenController extends Mage_Core_Controller_Front_Action
{
    public function totalAction()
    {
        echo Mage::helper('paymill/paymentHelper')->getAmount();
    }
}