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
$installer = $this;
$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS `{$this->getTable('paymill_log')}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `entry_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `version` varchar(25) NOT NULL COLLATE utf8_unicode_ci,
        `merchant_info` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
        `dev_info` text COLLATE utf8_unicode_ci DEFAULT NULL,
        `dev_info_additional` text COLLATE utf8_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;
    
    CREATE TABLE IF NOT EXISTS `{$this->getTable('paymill_fastCheckout')}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
        `client_id` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
        `cc_payment_id` varchar(250) COLLATE utf8_unicode_ci NULL,
        `elv_payment_id` varchar(250) COLLATE utf8_unicode_ci NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `userId` (`user_id`)
    ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;
");
    
$installer->run("UPDATE `{$this->getTable('sales_flat_quote_payment')}` SET method = 'paymill_creditcard' WHERE method = 'paymillcc';");

$installer->run("UPDATE `{$this->getTable('sales_flat_quote_payment')}` SET method = 'paymill_creditcard' WHERE method = 'paymillcc';");

$installer->run("UPDATE `{$this->getTable('sales_flat_quote_payment')}` SET method = 'paymill_directdebit' WHERE method = 'paymillelv';");

$installer->run("UPDATE `{$this->getTable('sales_flat_quote_payment')}` SET method = 'paymill_directdebit' WHERE method = 'paymillelv';");

$installer->endSetup();