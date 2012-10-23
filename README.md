Paymill-Magento
====================

Paymill extension for Magento (Version 1.5.x - 1.7.x)

# Installation

To install via Magento Connect login into your shop backend and go to System > Magento Connect > Magento Connect Manager and upload the PaymillPayment-1.0.1.tgz extension file from this repository. Afterwards go to System > Configuration > Payment Methods and configure the Paymill payment method (insert your Paymill test or live keys).

In case of any errors turn on the debug mode in the Paymill payment method configuration. Open the javascript console in your browser and check what's being logged during the checkout process. Additionally you can check the logfiles of your Magento installation (system.log and exception.log).

# Payment process

The payment is processed when an order is placed in the shop frontend.