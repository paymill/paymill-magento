PAYMILL-Magento Extension for credit card and direct debit payments
====================

PAYMILL extension for Magento compatible with: 1.5, 1.6, 1.6.1, 1.6.2.0, 1.7, 1.8 (tested for 1.7.2). This extension installs two payment methods: Credit card and direct debit.

##### Note: This is an all new version of the PAYMILL Magento Extension. 

## Your Advantages
* PCI DSS compatibility
* Payment means: Credit Card (Visa, Visa Electron, Mastercard, Maestro, Diners, Discover, JCB, AMEX), Direct Debit (ELV)
* Invoice generation on successful direct transactions with the payment means described above
* Refunds get created automatically on usage of the Magento creditmemo routine in you shop backend
* Optional configuration for authorization and manual capture with credit card payments
* Optional fast checkout configuration allowing your customers not to enter their payment detail over and over during checkout
* Improved payment form with visual feedback for your customers
* Supported Languages: German, English, Italian, Portuguese, French, Spanish
* Backend Log with custom View accessible from your shop backend
* support for the OneStepCheckout Extension

## PayFrame
 We've introduced a "payment form" option for easier compliance with PCI requirements.
 In addition to having a payment form directly integrated in your checkout page, you can
 use our embedded PayFrame solution to ensure that payment data never touches your
 website.

 PayFrame is enabled by default, but you can choose between both options in the plugin
 settings. Later this year, we're bringing you the ability to customise the appearance and
 text content of the PayFrame version.

 To learn more about the benefits of PayFrame, please visit our [FAQ](https://www.paymill.com/en/faq/how-does-paymills-payframe-solution-work "FAQ").
 
## Installation from this git repository

Download the complete module by using the link below:

[Latest Version](https://github.com/Paymill/Paymill-Magento/archive/master.zip)

To install the extension merge the contents of this cloned repository with your Magento installation.

## Configuration

Afterwards go to System > Configuration > Payment Methods and configure the PAYMILL payment methods you intend to use by inserting your PAYMILL test or live keys in the PAYMILL Basic Settings.

## Webhooks
To configure webhooks you have to open the webhook section in your magento admin, go to Paymill => Paymill Hooks => Add New
The url field should be prefilled. You can select between 3 event types (refund.succeeded, transaction.succeeded, chargeback.executed). Mulitiple selection possible.

For the transaction.succeeded a invoice and a order comment  will be created when the webhook arrives. For example after manuel a capture in the paymill cockpit.
For refund.succeeded with the full order amount a creditmemo and a order comment weill be created. For partial amounts only a order comment is added.
For chargeback.executed a order comment and a creditmemo will be created.

## In case of errors

In case of any errors turn on the debug mode and logging in the PAYMILL Basic Settings. Open the javascript console in your browser and check what's being logged during the checkout process. To access the logged information not printed in the console please refer to the PAYMILL Log in the admin backend.

## Notes about the payment process

The payment is processed when an order is placed in the shop frontend.
An invoice is being generated automatically.

There are several options altering this process:

Fast Checkout: Fast checkout can be enabled by selecting the option in the PAYMILL Basic Settings. If any customer completes a purchase while the option is active this customer will not be asked for data again. Instead a reference to the customer data will be saved allowing comfort during checkout.

Preauthorization and manual capture: If the option is selected, a preauthorization will be generated during checkout. On generation of the invoice, the capture will be triggered automatically, allowing easy capturing without the need to trigger it manually.
 
