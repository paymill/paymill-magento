Paymill-Magento Extension for credit card and direct debit payments
====================

Paymill extension for Magento (Tested on version 1.5.x - 1.7.x). This extension installs two payment methods: Credid card and direct debit. The second one is available in Germany only.

Paymill credit card form:

![Paymill creditcard payment form](https://raw.github.com/Paymill/Paymill-Magento/master/paymill/paymill_form_de.png)

Paymill direct debit form:

![Paymill creditcard payment form](https://raw.github.com/Paymill/Paymill-Magento/master/paymill/paymill_debit_form_de.png)

# Installation from this git repository 

Download the complete module by using the link below:
    
    https://github.com/Paymill/Paymill-Magento/archive/master.zip

To install the extension merge the contents of this cloned repository with your Magento installation. 

# Configuration

Afterwards go to System > Configuration > Payment Methods and configure the Paymill payment methods you intend to use (insert your Paymill test or live keys for each payment method).

In the configuration set API-URL to https://api.paymill.de/v2/.

# In case of errors

In case of any errors turn on the debug mode in the Paymill payment method configuration. Open the javascript console in your browser and check what's being logged during the checkout process. Additionally you can check the logfiles of your Magento installation (system.log and exception.log).

# Notes about the payment process

The payment is processed when an order is placed in the shop frontend. 

# Support for OneStepCheckout (onestepcheckout.com)

With some little adjustments the OneStepCheckout extension is supported for paymill. 

After 

    <script type="text/javascript">

in line 927 add:

    function paymill_onestep_callback_cc() {
      console.log("onestep callback");
      $('onestepcheckout-form').submit();
    }

And after

    var form = new VarienForm('onestepcheckout-form');

in line 948 add: 

    if (payment.currentMethod == 'paymillcc') {
      if (form.validator.validate()) {
        paymill_onestep_cc(paymill_onestep_callback_cc);
        return false;
      }
    }