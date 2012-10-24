Paymill-Magento
====================

Paymill extension for Magento (Tested on version 1.5.x - 1.7.x)

![Paymill creditcard payment form](https://raw.github.com/Paymill/Paymill-Magento/master/paymill/paymill_form_de.png)

# Installation

You have two installation options:

## Installation via Magento Connect

To install via Magento Connect login via your Magento shop backend and go to System > Magento Connect > Magento Connect Manager and upload the archive file contained in this repository (PaymillPayment-x.x.x.tgz).

## Installation from this git repository 

Use the following command to clone the complete repository including the submodules:
    
    git clone --recursive https://github.com/Paymill/Paymill-Magento.git

Merge the contents of this repository with your Magento installation. 

# Configuration

Afterwards go to System > Configuration > Payment Methods and configure the Paymill payment method (insert your Paymill test or live keys).

In case of any errors turn on the debug mode in the Paymill payment method configuration. Open the javascript console in your browser and check what's being logged during the checkout process. Additionally you can check the logfiles of your Magento installation (system.log and exception.log).

Depending on the Paymill API Version you use, select the Paymill-Wrapper version in the configuration.

# Notes about the payment process

The payment is processed when an order is placed in the shop frontend. 