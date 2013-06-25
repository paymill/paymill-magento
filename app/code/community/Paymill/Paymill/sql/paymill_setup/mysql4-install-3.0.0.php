<?php

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
    
    CREATE TABLE IF NOT EXISTS `{$this->getTable('paymill_transaction')}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
        `order_id` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
        `transaction_id` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
        `is_pre_authenticated` tinyint(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        UNIQUE KEY `order_id` (`order_id`)
    ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;
");
 
$installer->endSetup();