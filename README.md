Paymill-Magento
====================

Paymill extension for Magento (Tested on version 1.5.x - 1.7.x). This extension installs two payment methods: Credid card and direct debit. The second one is available in Germany only.

Paymill credit card form:

![Paymill creditcard payment form](https://raw.github.com/Paymill/Paymill-Magento/master/paymill/paymill_form_de.png)

Paymill direct debit form:

![Paymill creditcard payment form](https://raw.github.com/Paymill/Paymill-Magento/master/paymill/paymill_debit_form_de.png)

# Installation

You have two installation options:

## Installation via Magento Connect

To install via Magento Connect login via your Magento shop backend and go to System > Magento Connect > Magento Connect Manager and install via the Magento Connect Extension key (you get this in the Magento Connect marketplace).

## Installation from this git repository 

Use the following command to clone the complete repository including the submodules:
    
    git clone --recursive https://github.com/Paymill/Paymill-Magento.git

To install the extension merge the contents of this cloned repository with your Magento installation. 

# Configuration

Afterwards go to System > Configuration > Payment Methods and configure the Paymill payment method (insert your Paymill test or live keys).

# In case of errors

In case of any errors turn on the debug mode in the Paymill payment method configuration. Open the javascript console in your browser and check what's being logged during the checkout process. Additionally you can check the logfiles of your Magento installation (system.log and exception.log).

# Notes about Paymill API Version 2

Depending on the Paymill API Version you use, select the Paymill-Wrapper version in the configuration.

Note: On 31st of December, 2012 support for V1 ends. Please make sure that you migrate to V2 until then. To do so just switch to V2 in the configuration and insert https://api.paymill.de/v2/ as API URL.

# Notes about the payment process

The payment is processed when an order is placed in the shop frontend. 