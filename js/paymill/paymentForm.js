//Backend Options
var PAYMILL_PUBLIC_KEY = null;

//State Descriptors
var PAYMILL_PAYMENT_NAME = "Preparing Payment";
var PAYMILL_IMAGE_PATH = null;

//Errortexts
var PAYMILL_ERROR_STRING = "";

var eventFlag = false;


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
	if (pmQuery('.paymill-option-debug-' + getPaymillCode()).val() == '1') {
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
		
		console.log("[" + displayName + "]");
		console.log(message);
	}
}

/**
 * Event Handler for the display of the card icons
 */
function paymillShowCardIcon()
{
	var brand = paymill.cardType(pmQuery('#paymill_creditcard_number').val());
	brand = brand.toLowerCase();
	pmQuery("#paymill_creditcard_number")[0].className = pmQuery("#paymill_creditcard_number")[0].className.replace(/paymill-card-number-.*/g, '');
	if (brand !== 'unknown') {
		if (brand === 'american express') {
			brand = 'amex';
		}

		pmQuery('#paymill_creditcard_number').addClass("paymill-card-number-" + brand);
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
		var nv = {};
		
		nv['paymill-validate-' + getPaymillCode() + '-token'] = new Validator(
			'paymill-validate-' + getPaymillCode() + '-token',
			getValueIfExist('.paymill-payment-error-' + getPaymillCode() + '-token') + getValueIfExist('.PAYMILL_' + error.apierror + "-" + getPaymillCode()),
			function(v) {
				return v !== '';
			},
			''
		);

		Object.extend(Validation.methods, nv);
		debug(error);
		debug("Paymill Response Handler triggered: Error.");
	} else {
		// Appending Token to form
		debug("Saving Token in Form: " + result.token);
		pmQuery('.paymill-payment-token-' + getPaymillCode()).val(result.token);
	}
	
	pmQuery('#paymill-layer').removeClass("paymill-layer");
}

function getValueIfExist(selector)
{
	if ($$(selector)[0]) {
		return $$(selector)[0].value;
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
				
				pmQuery('#paymill-layer').addClass("paymill-layer");

				var cvc = '000';

				if (pmQuery('#paymill_creditcard_cvc').val() !== '') {
					cvc = pmQuery('#paymill_creditcard_cvc').val();
				}
				
				debug("Generating Token");
				paymill.createToken({
					amount_int: parseInt(getTokenAmount()),
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
				if (pmQuery('.paymill-info-sepa-elv').val() === 'false') {
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
				} else {
					var valid = pmQuery('#paymill_directdebit_holdername').val() !== ''
							 && pmQuery('#paymill_directdebit_iban').val() !== ''
							 && pmQuery('#paymill_directdebit_bic').val()  !== '';

					if (!valid) {
						return false;
					}

					debug("Generating Token");
					paymill.createToken({
						iban: pmQuery('#paymill_directdebit_iban').val(),
						bic: pmQuery('#paymill_directdebit_bic').val(),
						accountholder: pmQuery('#paymill_directdebit_holdername').val()
					}, paymillResponseHandler);
				}
			}
			break;
	}

	return false;
}

function getTokenAmount()
{	
	var returnVal = null;
	pmQuery.ajax({
		async: false,
		type: "POST",
		url: pmQuery('.paymill-payment-token-url-cc').val(),
	}).done(function(msg) {
		returnVal = msg;
	}).fail(function(jqXHR, textStatus) {
		// Appending error
		var nv = {};
		
		nv['paymill-validate-' + getPaymillCode() + '-token'] = new Validator(
			'paymill-validate-' + getPaymillCode() + '-token',
			getValueIfExist('.paymill-payment-error-' + getPaymillCode() + '-token') + " Amount not accessable. Reason: " + textStatus,
			function(v) {
				return v !== '';
			},
			''
		);

		Object.extend(Validation.methods, nv);
	});
	
	return returnVal;
}

function unsetElvValidationRules()
{
	var nvElv = {
		'paymill-validate-dd-holdername': new Validator(
			'paymill-validate-dd-holdername',
			'',
			function(v) {
				return true;
			},
			''
		),
		'paymill-validate-dd-iban': new Validator(
			'paymill-validate-dd-iban',
			getValueIfExist('.paymill-payment-error-iban-elv'),
			function(v) {
				return true;
			},
			''
		),
		'paymill-validate-dd-bic': new Validator(
			'paymill-validate-dd-bic',
			getValueIfExist('.paymill-payment-error-bic-elv'),
			function(v) {
				return true;
			},
			''
		),
		'paymill-validate-dd-account': new Validator(
			'paymill-validate-dd-account',
			'',
			function(v) {
				return true;
			},
			''
		),
		'paymill-validate-dd-bankcode': new Validator(
			'paymill-validate-dd-bankcode',
			'',
			function(v) {
				return true;
			},
			''
		)
	};

	Object.extend(Validation.methods, nvElv);
}

function unsetCcValidationRules()
{
	var nvCc = {
		'paymill-validate-cc-number': new Validator(
			'paymill-validate-cc-number',
			'',
			function(v) {
				return true;
			},
			''
		),
		'paymill-validate-cc-expdate-month': new Validator(
			'paymill-validate-cc-expdate-month',
			'',
			function(v) {
				return true;
			},
			''
		),
		'paymill-validate-cc-expdate-year': new Validator(
			'paymill-validate-cc-expdate-year',
			'',
			function(v) {
				return true;
			},
			''
		),
		'paymill-validate-cc-holder': new Validator(
			'paymill-validate-cc-holder',
			'',
			function(v) {
				return true;
			},
			''
		),
		'paymill-validate-cc-cvc': new Validator(
			'paymill-validate-cc-cvc',
			'',
			function(v) {
				return true;
			},
			''
		)
	};

	Object.extend(Validation.methods, nvCc);
}

function setElvValidationRules()
{
	var nvElv = {
		'paymill-validate-dd-holdername': new Validator(
			'paymill-validate-dd-holdername',
			getValueIfExist('.paymill-payment-error-holder-elv'),
			function(v) {
				return !(v === '');
			},
			''
		),
		'paymill-validate-dd-iban': new Validator(
			'paymill-validate-dd-iban',
			getValueIfExist('.paymill-payment-error-iban-elv'),
			function(v) {
				return !(v === '');
			},
			''
		),
		'paymill-validate-dd-bic': new Validator(
			'paymill-validate-dd-bic',
			getValueIfExist('.paymill-payment-error-bic-elv'),
			function(v) {
				return !(v === '');
			},
			''
		),
		'paymill-validate-dd-account': new Validator(
			'paymill-validate-dd-account',
			getValueIfExist('.paymill-payment-error-number-elv'),
			function(v) {
				return paymill.validateAccountNumber(v);
			},
			''
		),
		'paymill-validate-dd-bankcode': new Validator(
			'paymill-validate-dd-bankcode',
			getValueIfExist('.paymill-payment-error-bankcode'),
			function(v) {
				return paymill.validateBankCode(v);
			},
			''
		)
	};

	Object.extend(Validation.methods, nvElv);
}

function setCcValidationRules()
{
	var nvCc = {
		'paymill-validate-cc-number': new Validator(
			'paymill-validate-cc-number',
			getValueIfExist('.paymill-payment-error-number'),
			function(v) {
				return paymill.validateCardNumber(v);
			},
			''
		),
		'paymill-validate-cc-expdate-month': new Validator(
			'paymill-validate-cc-expdate-month',
			getValueIfExist('.paymill-payment-error-expdate'),
			function(v) {
				return paymill.validateExpiry(pmQuery('#paymill_creditcard_expiry_month').val(), pmQuery('#paymill_creditcard_expiry_year').val());
			},
			''
		),
		'paymill-validate-cc-expdate-year': new Validator(
			'paymill-validate-cc-expdate-year',
			getValueIfExist('.paymill-payment-error-expdate'),
			function(v) {
				return paymill.validateExpiry(pmQuery('#paymill_creditcard_expiry_month').val(), pmQuery('#paymill_creditcard_expiry_year').val());
			},
			''
		),
		'paymill-validate-cc-holder': new Validator(
			'paymill-validate-cc-holder',
			getValueIfExist('.paymill-payment-error-holder'),
			function(v) {
				return (paymill.validateHolder(v));
			},
			''
		),
		'paymill-validate-cc-cvc': new Validator(
			'paymill-validate-cc-cvc',
			getValueIfExist('.paymill-payment-error-cvc'),
			function(v) {
				if (paymill.cardType(pmQuery('#paymill_creditcard_number').val()).toLowerCase() === 'maestro') {
					return true;
				}

				return paymill.validateCvc(v);
			},
			''
		)
	};

	Object.extend(Validation.methods, nvCc);
}

function addPaymillEvents()
{
	setElvValidationRules();
	
	setCcValidationRules();
	
	if (pmQuery('.paymill-info-fastCheckout-elv').val() === 'true') {
		unsetElvValidationRules();
	}
	
	if (pmQuery('.paymill-info-fastCheckout-cc').val() === 'true') {
		unsetCcValidationRules();
	}
	
	if (!eventFlag) {
		pmQuery('#paymill_directdebit_iban').keyup(function() {
			var iban = pmQuery('#paymill_directdebit_iban').val();
			if (!iban.match(/^DE/)) {
				var newVal = "DE";
				if (iban.match(/^.{2}(.*)/)) {
					newVal += iban.match(/^.{2}(.*)/)[1];
				}
				pmQuery('#paymill_directdebit_iban').val(newVal);
			}
		});
		
		pmQuery('#paymill_directdebit_iban').trigger('keyup');

		
		pmQuery('#paymill_directdebit_holdername').live('focus', function() {
			setElvValidationRules();
			pmQuery('.paymill-info-fastCheckout-elv').val('false');
		});

		pmQuery('#paymill_directdebit_account').live('focus', function() {
			setElvValidationRules();
			pmQuery('.paymill-info-fastCheckout-elv').val('false');
		});

		pmQuery('#paymill_directdebit_bankcode').live('focus', function() {
			setElvValidationRules();
			pmQuery('.paymill-info-fastCheckout-elv').val('false');
		});

		pmQuery('#paymill_directdebit_iban').live('focus', function() {
			setElvValidationRules();
			pmQuery('.paymill-info-fastCheckout-elv').val('false');
		});

		pmQuery('#paymill_directdebit_bic').live('focus', function() {
			setElvValidationRules();
			pmQuery('.paymill-info-fastCheckout-elv').val('false');
		});

		pmQuery('#paymill_creditcard_holdername').live('focus', function() {
			setCcValidationRules();
			pmQuery('.paymill-info-fastCheckout-cc').val('false');
		});

		pmQuery('#paymill_creditcard_cvc').live('focus', function() {
			setCcValidationRules();
			pmQuery('.paymill-info-fastCheckout-cc').val('false');
		});

		pmQuery('#paymill_creditcard_number').live('focus', function() {
			setCcValidationRules();
			pmQuery('.paymill-info-fastCheckout-cc').val('false');
		});

		pmQuery('#paymill_creditcard_expiry_month').live('change', function() {
			setCcValidationRules();
			pmQuery('.paymill-info-fastCheckout-cc').val('false');
		});

		pmQuery('#paymill_creditcard_expiry_year').live('change', function() {
			setCcValidationRules();
			pmQuery('.paymill-info-fastCheckout-cc').val('false');
		});

		pmQuery('#paymill_creditcard_number').live('input', function() {
			paymillShowCardIcon();
		});

		pmQuery('#paymill_creditcard_cvc').live('input', function() {
			paymillSubmitForm();
		});

		pmQuery('#paymill_creditcard_expiry_month').live('change', function() {
			paymillSubmitForm();
		});

		pmQuery('#paymill_creditcard_expiry_year').live('change', function() {
			paymillSubmitForm();
		});

		pmQuery('#paymill_directdebit_bankcode').live('input', function() {
			paymillSubmitForm();
		});

		pmQuery('#paymill_directdebit_account').live('input', function() {
			paymillSubmitForm();
		});

		pmQuery('#paymill_directdebit_iban').live('input', function() {
			paymillSubmitForm();
		});

		pmQuery('#paymill_directdebit_bic').live('input', function() {
			paymillSubmitForm();
		});

		pmQuery('#paymill_directdebit_holdername').live('input', function() {
			paymillSubmitForm();
		});

		pmQuery('#paymill_creditcard_number').live('input', function() {
			paymillSubmitForm();
		});
		
		eventFlag = true;
	}
}