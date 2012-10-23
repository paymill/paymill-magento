Paymill-Magento
====================

Paymill extension for Magento (Version 1.5.x - 1.7.x)

# Installation and configuration

Use the following command to clone the complete repository including the submodules:
    
    git clone --recursive https://github.com/Paymill/Paymill-Shopware4.git

To install via Magento Connect login into your shop backend and go to System > Magento Connect > Magento Connect Manager and merge the contents of this repository with your magento installation. Afterwards go to System > Configuration > Payment Methods and configure the Paymill payment method (insert your Paymill test or live keys).

In case of any errors turn on the debug mode in the Paymill payment method configuration. Open the javascript console in your browser and check what's being logged during the checkout process. Additionally you can check the logfiles of your Magento installation (system.log and exception.log).

Depending on the Paymill API Version you use, select the Paymill-Wrapper version in the configuration.

# Payment process

The payment is processed when an order is placed in the shop frontend.