Paymill-Magento Extension for credit card and direct debit payments
====================

Paymill extension for Magento (tested for 1.7.2). This extension installs two payment methods: Credit card and direct debit.

##### Note: This is an all new version of the Paymill Magento Extension. 

## Your Advantages
* PCI DSS compatibility
* Payment means: Credit Card, Direct Debit
* Invoice generation on successful direct transactions with the payment means described above
* Refunds get created automatically on usage of the Magento creditmemo routine in you shop backend
* Optional configuration for authorization and manual capture with credit card payments
* Optional fast checkout configuration allowing your customers not to enter their payment detail over and over during checkout
* Improved payment form with visual feedback for your customers
* Supported Languages: German, English
* Backend Log with custom View accessible from your shop backend

## Installation from this git repository

Download the complete module by using the link below:

[Latest Version](https://github.com/Paymill/Paymill-Magento/archive/master.zip)

To install the extension merge the contents of this cloned repository with your Magento installation.

## Configuration

Afterwards go to System > Configuration > Payment Methods and configure the Paymill payment methods you intend to use by inserting your Paymill test or live keys in the Paymill Basic Settings.

## In case of errors

In case of any errors turn on the debug mode and logging in the Paymill Basic Settings. Open the javascript console in your browser and check what's being logged during the checkout process. To access the logged information not printed in the console please refer to the Paymill Log in the admin backend.

## Notes about the payment process

The payment is processed when an order is placed in the shop frontend.
An invoice is being generated automatically.

There are several options altering this process:

Fast Checkout: Fast checkout can be enabled by selecting the option in the Paymill Basic Settings. If any customer completes a purchase while the option is active this customer will not be asked for data again. Instead a reference to the customer data will be saved allowing comfort during checkout.

Preauthorization and manual capture: If the option is selected, a preauthorization will be generated during checkout. On generation of the invoice, the capture will be triggered automatically, allowing easy capturing without the need to trigger it manually.
 
