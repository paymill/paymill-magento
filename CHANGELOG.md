## v4.0.4
 * Added modman file for composer
 * Added wsdl.xml to allow setting `paymill-payment-token-cc` value through SOAP v2 call using `shoppingCartPaymentMethod`
 * Updated support list in README.md
 * Changed default layout handle to specific checkout layout handles to avoid loading files on every request

## v4.0.3
 * fix SUPEE-6788 related admin controller issue
 * add role managment for paymill log and webhook managment

## v4.0.2
 * add paymill api errors to translation engine
 * initialize paymillPci js variable for both creditcard cases

## v4.0.1
 * improve payment method selection in the javascript

## v4.0.0
 * Integration of PayFrame to enable use of SAQ A for easier PCI DSS 3.0 compliance.

## v3.9.3
 * remove usage of Varien_Date::toTimestamp() to avoid incompatibility to Magento 1.5.x.x

## v3.9.2
 * add a javascript fallback for Array.forEach to support the token selector feature also for IE 8

## v3.9.1
 * make invoice creation for webhooks "transaction.succeeded" event configurable

## v3.9.0
 * add webhook support for the following events (refund.succeeded, transaction.succeeded, chargeback.executed)

## v3.8.1
 * hotfix token creation creditcard with prototype selector

## v3.8.0
 * switch for base and order currency
 * rewrite js to remove jQuery usage and only use the magento native Prototype library
 * allow to configure a prototype selector for the token creation

## v3.7.0
 * better name for set/addText block in layout.xml and and change setText to addText

## v3.6.9
 * fix for source field (for internal paymill shop identification)
 * description text cut off after 128 signs

## v3.6.8
 * The payment will now done with the Base Currency and Base Grand Total

## v3.6.7
 * Updated the feature for showing the credit card logos. Now it is possible to use the old behaviour by setting "Show Credit Card Logos" to No.
 * fixed a bug, where the invoice had an other currency then the order.
 * fixed design issue with the credit card logos in Magento 1.9

## v3.6.6
 * changed CVC field design to Magento CVC design with "What is this" Link and Popup
 * added an option which allows the following credit cards logos to be shown checkout:
	- Visa
	- MasterCard
	- American Express
	- CartaSi
	- Carte Bleue
	- Diners Club
	- JCB
	- Maestro
	- China UnionPay
	- Discover Card
	- Dankort
 * The two payment forms ELV and SEPA were merged to one form.
 * added Prenotification for SEPA (Day of Debit will be shown on the invoice and on the order confirmation mail)
 * updated PHP Wrapper

## v3.6.5
 * added BIC Validation (Lengthcheck)
 * added IBAN Validation
 * added languages it, fr, es, pt and updated en, de
 * improved CC Brand detection with greyscale Logos if Brand was detected but number not valid yet

## v3.6.4
 * remove prefilled stars from the payment form
 * avoid double token creation

## v3.6.3
 * improve javascript
 * fix problem when a token error occurs
 * online offline refund compatibility added

## v3.6.2
 * add detectCardBrand
 * add configurable checkout text

## v3.6.1
 * sepa fix

## v3.6.0
 * add elv sepa

## v3.5.9
 * set existing invoices to paid

## v3.5.8
 * log token errors

## v3.5.7
 * remove not empty validation for token field add the validation only in case of a token generating error
 * no internal order possible

## v3.5.6
 * get token amount via ajax

## v3.5.5
 * expend js validation to paymill token field

## v3.5.4
 * set invoice state to paid
 * selectable order states

## v3.5.3
 * fix broken image in pdf invoice

## v3.5.2
 * check if payments and clients exist
 * remove PAYMILL label
 * send invoice mail only when the option is setted
 * better js validation
 * set order state to processing after a automatic invoice creation

## v3.5.1
 * move token validation from assignData to authorize

## v3.5.0
 * remove empty input fields while focusing
 * automatic email after successful invoice creation

## v3.4.3
 * avoid input field jumping during the validation

## v3.4.2
 * only validate the paymill form instead the complete checkout form
 * fix not working crdeit card icons

## v3.4.1
 * fix magento compiler problems
 * jQuery compatibility when other versions loaded

## v3.4.0
 * Customer friendly error messages
 * Better owner validation
 * Global css and js loading

## v3.3.1
 * Receive customer email saver
 * Change event binding sequence in paymentForm.js

## v3.3.0
 * 1.8 compatibility (just in Magento Connect, the plugin was compatible Magento 1.8)
 * add backward compatibility for 2.x orders
 * Better js event handling
 * hidden inputs fields now unique
 * Extend global record search with paymill log entrys

## v3.2.0
 * Detail log view
 * Enhanced fast checkout
 * move pre-auth/capture option to paymill cc config
 * js redesign
 * Support for one step checkout plugins
 * Maestro handling (no cvc)

## v3.1.0
 * payment form styling
 * better token validation

## v3.0.6
 * Fixed a bug causing the plugin to crash during invoice creation
 * Payment Methods will now only be displayed if the Keys are set in the config
 * Fixed a bug causing warnings in the Magento systemlog in multiple cases

## v3.0.4
 * Payment template changed
 * several bugfixes

## v3.0.0

 * Rewrote form scratch