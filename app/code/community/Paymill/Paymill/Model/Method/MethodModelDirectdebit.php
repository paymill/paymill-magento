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
class Paymill_Paymill_Model_Method_MethodModelDirectdebit extends Paymill_Paymill_Model_Method_MethodModelAbstract
{

    /**
     * Magento method code
     *
     * @var string
     */
    protected $_code = "paymill_directdebit";

    /**
     * Form block identifier
     *
     * @var string
     */
    protected $_formBlockType = 'paymill/payment_form_paymentFormDirectdebit';

    /**
     * Info block identifier
     *
     * @var string
     */
    protected $_infoBlockType = 'paymill/payment_info_paymentFormDirectdebit';

}
