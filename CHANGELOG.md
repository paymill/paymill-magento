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