var eventsSetted = false;

//Backend Options
var PAYMILL_PUBLIC_KEY = null;

//State Descriptors
var PAYMILL_PAYMENT_NAME = "Preparing Payment";
var PAYMILL_IMAGE_PATH = null;

//Errortexts
var PAYMILL_ERROR_STRING = "";

PAYMILL_ERROR_TEXT_IVALID_NUMBER_CC = getValueIfExist('.paymill-payment-error-number');
PAYMILL_ERROR_TEXT_IVALID_HOLDER_CC = getValueIfExist('.paymill-payment-error-holder');
PAYMILL_ERROR_TEXT_IVALID_CVC = getValueIfExist('.paymill-payment-error-cvc');
PAYMILL_ERROR_TEXT_IVALID_EXPDATE = getValueIfExist('.paymill-payment-error-expdate');

PAYMILL_ERROR_TEXT_IVALID_NUMBER_ELV = getValueIfExist('.paymill-payment-error-number-elv');
PAYMILL_ERROR_TEXT_IVALID_HOLDER_ELV = getValueIfExist('.paymill-payment-error-holder-elv')
PAYMILL_ERROR_TEXT_IVALID_BANKCODE = getValueIfExist('.paymill-payment-error-bankcode');

var nvElv = {
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

var nvCc = {
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

function getValueIfExist(selector)
{
	if ($$('.initials')[0]) {
		return $$('.initials')[0].value;
	}
	
	return '';
}

/**
 * 
 * @returns {Boolean}
 */
function paymillSubmitForm()
{
	PAYMILL_PUBLIC_KEY = pmQuery('.paymill-info-public_key-' + getPaymillCode()).val();
	PAYMILL_PAYMENT_NAME = pmQuery("input[name='payment[method]']:checked").val();
	
	switch (PAYMILL_PAYMENT_NAME) {
		case "paymill_creditcard":
			paymill.config('3ds_cancel_label', pmQuery('.paymill_3ds_cancel').val());
			if (pmQuery('.paymill-info-fastCheckout-cc').val() === 'false') {
				var valid = (paymill.validateCvc(pmQuery('#paymill_creditcard_cvc').val()) || paymill.cardType(pmQuery('#paymill_creditcard_number').val()).toLowerCase() === 'maestro')
						 && paymill.validateHolder(pmQuery('#paymill_creditcard_holdername').val()) 
						 && paymill.validateExpiry(pmQuery('#paymill_creditcard_expiry_month').val(), pmQuery('#paymill_creditcard_expiry_year').val()) 
						 && paymill.validateCardNumber(pmQuery('#paymill_creditcard_number').val());
				 
				if (!valid) {
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
				var valid = pmQuery('#paymill_directdebit_holdername').val() !== ''
						 && paymill.validateAccountNumber(pmQuery('#paymill_directdebit_account').val())
						 && paymill.validateBankCode(pmQuery('#paymill_directdebit_bankcode').val());

				if (!valid) {
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

function paymillSubmit()
{
	alert('paymillSubmit');
	PAYMILL_PAYMENT_NAME = pmQuery("input[name='payment[method]']:checked").val();
	var form = pmQuery("input[name='payment[method]']:checked").closest("form").attr("id");
	var paymillValidator = new Validation(form);
	switch (PAYMILL_PAYMENT_NAME) {
		case "paymill_creditcard":
			if (pmQuery('.paymill-info-fastCheckout-cc').val() === 'false') {
				Object.extend(Validation.methods, nvCc);
				paymillValidator.validate();
			}
			break;
		case "paymill_directdebit":
			if (pmQuery('.paymill-info-fastCheckout-elv').val() === 'false') {
				Object.extend(Validation.methods, nvElv);
				paymillValidator.validate();
			}
			break;
	}
}

function addPaymillEvents()
{
	if (pmQuery('#payment-buttons-container button:first')) {
		pmQuery('#payment-buttons-container button:first').on("click", paymillSubmit);
	}
	
	if (pmQuery('#onestepcheckout-place-order')) {
		pmQuery('#onestepcheckout-place-order').on("click", paymillSubmit);
	}
	
	pmQuery('#paymill_directdebit_holdername').on('focus', function() {
		pmQuery('.paymill-info-fastCheckout-elv').val('false');
	});

	pmQuery('#paymill_directdebit_account').on('focus', function() {
		pmQuery('.paymill-info-fastCheckout-elv').val('false');
	});

	pmQuery('#paymill_directdebit_bankcode').on('focus', function() {
		pmQuery('.paymill-info-fastCheckout-elv').val('false');
	});

	pmQuery('#paymill_creditcard_holdername').on('focus', function() {
		pmQuery('.paymill-info-fastCheckout-cc').val('false');
	});

	pmQuery('#paymill_creditcard_cvc').on('focus', function() {
		pmQuery('.paymill-info-fastCheckout-cc').val('false');
	});

	pmQuery('#paymill_creditcard_number').on('focus', function() {
		pmQuery('.paymill-info-fastCheckout-cc').val('false');
	});

	pmQuery('#paymill_creditcard_expiry_month').on('change', function() {
		pmQuery('.paymill-info-fastCheckout-cc').val('false');
	});

	pmQuery('#paymill_creditcard_expiry_year').on('change', function() {
		pmQuery('.paymill-info-fastCheckout-cc').val('false');
	});

	pmQuery('#paymill_creditcard_number').on('input', paymillSubmitForm);
	pmQuery('#paymill_creditcard_cvc').on('input', paymillSubmitForm);
	pmQuery('#paymill_creditcard_expiry_month').on('change', paymillSubmitForm);
	pmQuery('#paymill_creditcard_expiry_year').on('change', paymillSubmitForm);
	pmQuery('#paymill_directdebit_bankcode').on('input', paymillSubmitForm);
	pmQuery('#paymill_creditcard_number').on('input', paymillShowCardIcon);
	eventsSetted = true;
}