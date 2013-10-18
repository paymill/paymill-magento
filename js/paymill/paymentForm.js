var eventsSetted = false;

//Backend Options
var PAYMILL_PUBLIC_KEY = null;

//State Descriptors
var PAYMILL_PAYMENT_NAME = "Preparing Payment";
var PAYMILL_IMAGE_PATH = null;

//Errortexts
var PAYMILL_ERROR_STRING = "";
var PAYMILL_ERROR_TEXT_IVALID_NUMBER = null;
var PAYMILL_ERROR_TEXT_IVALID_EXPDATE = null;
var PAYMILL_ERROR_TEXT_IVALID_HOLDER = null;
var PAYMILL_ERROR_TEXT_IVALID_BANKCODE = null;
var PAYMILL_ERROR_TEXT_IVALID_PAYMENT = null;


function getPaymillCode()
{
	var methods = {
		paymill_creditcard: "cc",
		paymill_directdebit: 'elv'
	 };
	
	if (methods.hasOwnProperty(pmQuery("input[name='payment[method]']:checked").val())) {
		return methods[pmQuery("input[name='payment[method]']:checked").val()];
	}
	
	return 'other';
}

/**
 * prints debug messages in the log if debug mode is active
 * @param {String} message
 */
function debug(message)
{
	debug_state = pmQuery('.paymill-option-debug-' + getPaymillCode()).val();
	if (debug_state === 1) {
		var displayName = "";
		if (PAYMILL_PAYMENT_NAME === 'paymill_creditcard') {
			displayName = 'Credit Card';
		}
		if (PAYMILL_PAYMENT_NAME === 'paymill_directdebit') {
			displayName = 'Direct Debit';
		}
		if (PAYMILL_PAYMENT_NAME === 'Preparing Payment') {
			displayName = 'Preparing Payment';
		}

		console.log("[" + displayName + "] " + message);
	}
}

/**
 * Event Handler for the display of the card icons
 */
function paymillShowCardIcon()
{
	var cssClass = "input-text paymill-validate-cc-number required-entry paymill_creditcard_number-";
	switch (paymill.cardType(pmQuery('#paymill_creditcard_number').val()).toLowerCase()) {
		case 'visa':
			pmQuery('#paymill_creditcard_number').removeClass();
			pmQuery('#paymill_creditcard_number').addClass(cssClass + 'visa');
			break;
		case 'mastercard':
			pmQuery('#paymill_creditcard_number').removeClass();
			pmQuery('#paymill_creditcard_number').addClass(cssClass + 'mastercard');
			break;
		case 'american express':
			pmQuery('#paymill_creditcard_number').removeClass();
			pmQuery('#paymill_creditcard_number').addClass(cssClass + 'american');
			break;
		case 'jcb':
			pmQuery('#paymill_creditcard_number').removeClass();
			pmQuery('#paymill_creditcard_number').addClass(cssClass + 'jcb');
			break;
		case 'maestro':
			pmQuery('#paymill_creditcard_number').removeClass();
			pmQuery('#paymill_creditcard_number').addClass(cssClass + 'maestro');
			break;
		case 'diners club':
			pmQuery('#paymill_creditcard_number').removeClass();
			pmQuery('#paymill_creditcard_number').addClass(cssClass + 'diners');
			break;
		case 'discover':
			pmQuery('#paymill_creditcard_number').removeClass();
			pmQuery('#paymill_creditcard_number').addClass(cssClass + 'discover');
			break;
		case 'unionpay':
			pmQuery('#paymill_creditcard_number').removeClass();
			pmQuery('#paymill_creditcard_number').addClass(cssClass + 'unionpay');
			break;
	}
}

/**
 * Handles the response of the paymill bridge. saves the token in a formfield.
 * @param {Boolean} error
 * @param {response object} result
 */
function paymillResponseHandler(error, result)
{
	if (error) {
		// Appending error
		PAYMILL_ERROR_STRING += error.apierror + "\n";
		debug(error.apierror);
		debug("Paymill Response Handler triggered: Error.");
	} else {
		// Appending Token to form
		debug("Saving Token in Form: " + result.token);
		pmQuery('.paymill-payment-token-' + getPaymillCode()).val(result.token);
	}
}
/**
 * 
 * @returns {Boolean}
 */
function paymillSubmitForm()
{
	PAYMILL_PUBLIC_KEY = pmQuery('.paymill-info-public_key-' + getPaymillCode()).val();
	PAYMILL_PAYMENT_NAME = pmQuery("input[name='payment[method]']:checked").val();

	if (PAYMILL_PAYMENT_NAME === "paymill_creditcard") {
		paymill.config('3ds_cancel_label', pmQuery('.paymill_3ds_cancel').val());
		PAYMILL_ERROR_TEXT_IVALID_NUMBER_CC = pmQuery('.paymill-payment-error-number').val();
		PAYMILL_ERROR_TEXT_IVALID_HOLDER_CC = pmQuery('.paymill-payment-error-holder').val();
		PAYMILL_ERROR_TEXT_IVALID_CVC = pmQuery('.paymill-payment-error-cvc').val();
		PAYMILL_ERROR_TEXT_IVALID_EXPDATE = pmQuery('.paymill-payment-error-expdate').val();
	}

	if (PAYMILL_PAYMENT_NAME === "paymill_directdebit") {
		PAYMILL_ERROR_TEXT_IVALID_NUMBER_ELV = pmQuery('.paymill-payment-error-number-elv').val();
		PAYMILL_ERROR_TEXT_IVALID_HOLDER_ELV = pmQuery('.paymill-payment-error-holder-elv').val();
		PAYMILL_ERROR_TEXT_IVALID_BANKCODE = pmQuery('.paymill-payment-error-bankcode').val();
	}

	
	if (pmQuery('#paymill_creditcard_number').closest("form").attr("id") === undefined) {
		pmQuery('#paymill_creditcard_number').closest("form").attr("id", 'paymill-checkout-form')
	}
	
	var form = pmQuery('#paymill_creditcard_number').closest("form").attr("id");

	switch (PAYMILL_PAYMENT_NAME) {
		case "paymill_creditcard":
			if (pmQuery('.paymill-info-fastCheckout-cc').val() === 'false') {
				var paymillValidator = new Validation(form);
				var nv = {
					'paymill-validate-cc-number': new Validator(
						'paymill-validate-cc-number',
						PAYMILL_ERROR_TEXT_IVALID_NUMBER_CC,
						function(v) {
							return paymill.validateCardNumber(v);
						},
						''
					),
					'paymill-validate-cc-expdate-month': new Validator(
						'paymill-validate-cc-expdate-month',
						PAYMILL_ERROR_TEXT_IVALID_EXPDATE,
						function(v) {
							return paymill.validateExpiry(pmQuery('#paymill_creditcard_expiry_month').val(), pmQuery('#paymill_creditcard_expiry_year').val());
						},
						''
					),
					'paymill-validate-cc-expdate-year': new Validator(
						'paymill-validate-cc-expdate-year',
						PAYMILL_ERROR_TEXT_IVALID_EXPDATE,
						function(v) {
							return paymill.validateExpiry(pmQuery('#paymill_creditcard_expiry_month').val(), pmQuery('#paymill_creditcard_expiry_year').val());
						},
						''
					),
					'paymill-validate-cc-holder': new Validator(
						'paymill-validate-cc-holder',
						PAYMILL_ERROR_TEXT_IVALID_HOLDER_CC,
						function(v) {
							return (paymill.validateHolder(v));
						},
						''
					),
					'paymill-validate-cc-cvc': new Validator(
						'paymill-validate-cc-cvc',
						PAYMILL_ERROR_TEXT_IVALID_CVC,
						function(v) {
							if (paymill.cardType(pmQuery('#paymill_creditcard_number').val()).toLowerCase() === 'maestro') {
								return true;
							}
							
							return paymill.validateCvc(v);
						},
						''
					)
				};

				Object.extend(Validation.methods, nv);

				if (!paymillValidator.validate()) {
					return false;
				}
				
				var cvc = '000';
				
				if (pmQuery('#paymill_creditcard_cvc').val() !== '') {
					cvc = pmQuery('#paymill_creditcard_cvc').val();
				}

				debug("Generating Token");
				paymill.createToken({
					amount_int: parseInt(pmQuery('.paymill-payment-amount-' + getPaymillCode()).val()), // E.g. "15" for 0.15 Eur
					currency: pmQuery('.paymill-payment-currency-' + getPaymillCode()).val(), // ISO 4217 e.g. "EUR"
					number: pmQuery('#paymill_creditcard_number').val(),
					exp_month: pmQuery('#paymill_creditcard_expiry_month').val(),
					exp_year: pmQuery('#paymill_creditcard_expiry_year').val(),
					cvc: cvc,
					cardholder: pmQuery('#paymill_creditcard_holdername').val()
				}, paymillResponseHandler);
			}
			break;
		case "paymill_directdebit":
			if (pmQuery('.paymill-info-fastCheckout-elv').val() === 'false') {
				var paymillValidator = new Validation(form);
				var nv = {
					'paymill-validate-dd-holdername': new Validator(
						'paymill-validate-dd-holdername',
						PAYMILL_ERROR_TEXT_IVALID_HOLDER_ELV,
						function(v) {
							return !(v === '');
						},
						''
					),
					'paymill-validate-dd-account': new Validator(
						'paymill-validate-dd-account',
						PAYMILL_ERROR_TEXT_IVALID_NUMBER_ELV,
						function(v) {
							return paymill.validateAccountNumber(v);
						},
						''
					),
					'paymill-validate-dd-bankcode': new Validator(
						'paymill-validate-dd-bankcode',
						PAYMILL_ERROR_TEXT_IVALID_BANKCODE,
						function(v) {
							return paymill.validateBankCode(v);
						},
						''
					)
				};

				Object.extend(Validation.methods, nv);

				if (!paymillValidator.validate()) {
					return false;
				}

				debug("Generating Token");
				paymill.createToken({
					number: pmQuery('#paymill_directdebit_account').val(),
					bank: pmQuery('#paymill_directdebit_bankcode').val(),
					accountholder: pmQuery('#paymill_directdebit_holdername').val()
				}, paymillResponseHandler);
			}
			break;
	}

	return false;
}

function addPaymillEvents()
{

	pmQuery('#paymill_directdebit_holdername').live('focus', function() {
		pmQuery('.paymill-info-fastCheckout-elv').val('false');
		pmQuery('#paymill_directdebit_holdername').val('');
	});

	pmQuery('#paymill_directdebit_account').live('focus', function() {
		pmQuery('.paymill-info-fastCheckout-elv').val('false');
		pmQuery('#paymill_directdebit_account').val('');
	});


	pmQuery('#paymill_directdebit_bankcode').live('focus', function() {
		pmQuery('.paymill-info-fastCheckout-elv').val('false');
		pmQuery('#paymill_directdebit_bankcode').val('');
	});

	pmQuery('#paymill_creditcard_holdername').live('focus', function() {
		pmQuery('.paymill-info-fastCheckout-cc').val('false');
		pmQuery('#paymill_creditcard_holdername').val('');
	});

	pmQuery('#paymill_creditcard_cvc').live('focus', function() {
		pmQuery('.paymill-info-fastCheckout-cc').val('false');
	});

	pmQuery('#paymill_creditcard_number').live('focus', function() {
		pmQuery('.paymill-info-fastCheckout-cc').val('false');
	});

	pmQuery('#paymill_creditcard_expiry_month').live('change', function() {
		pmQuery('.paymill-info-fastCheckout-cc').val('false');
	});

	pmQuery('#paymill_creditcard_expiry_year').live('change', function() {
		pmQuery('.paymill-info-fastCheckout-cc').val('false');
	});

	pmQuery('#paymill_creditcard_number').live('input', paymillSubmitForm);
	pmQuery('#paymill_creditcard_cvc').live('input', paymillSubmitForm);
	pmQuery('#paymill_creditcard_expiry_month').live('change', paymillSubmitForm);
	pmQuery('#paymill_creditcard_expiry_year').live('change', paymillSubmitForm);
	pmQuery('#paymill_directdebit_bankcode').live('input', paymillSubmitForm);
	pmQuery('#paymill_creditcard_number').live('input', paymillShowCardIcon);
	eventsSetted = true;
}