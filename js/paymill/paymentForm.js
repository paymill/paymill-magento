var PAYMILL_PUBLIC_KEY = null;

function Paymill()
{
	this.paymillSelectedPaymentName = "Preparing Payment";
	this.eventFlag = false;
	
}

Paymill.prototype.setPaymillCode = function(code)
{
	this.paymillCode = code;
}

Paymill.prototype.setCodes = function()
{
	this.paymillCc = 'paymill_creditcard';
	this.paymillElv = 'paymill_directdebit';
}

Paymill.prototype.getPaymillCode = function()
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
Paymill.prototype.debug = function(message)
{
	debugState = pmQuery('.paymill-option-debug-' + this.getPaymillCode()).val();
	if (debugState === "1") {
		var displayName = "";
		if (this.paymillSelectedPaymentName === this.paymillCc) {
			displayName = 'Credit Card';
		}

		if (this.paymillSelectedPaymentName === this.paymillElv) {
			displayName = 'Direct Debit';
		}

		if (this.paymillSelectedPaymentName === 'Preparing Payment') {
			displayName = 'Preparing Payment';
		}

		console.log("[" + displayName + "] " + message);
	}
}

Paymill.prototype.detectCreditcardBranding = function(creditcardNumber)
{
	var brand = 'unknown';
	if (creditcardNumber.match(/^\d{6}/)) {
		switch (true) {
			case /^(415006|497|407497|513)/.test(creditcardNumber):
				brand = "carte-bleue";
				break;
			case /^(45399[78]|432913|5255)/.test(creditcardNumber):
				brand = "carta-si";
				break;
			case /^(4571|5019)/.test(creditcardNumber):
				brand = "dankort";
				break;
			case /^(62|88)/.test(creditcardNumber):
				brand = "unionpay";
				break;
			case /^6(011|5)/.test(creditcardNumber):
				brand = "discover";
				break;
			case /^3(0[0-5]|[68])/.test(creditcardNumber):
				brand = "diners";
				break;
			case /^(5018|5020|5038|5893|6304|6759|6761|6762|6763|0604|6390)/.test(creditcardNumber):
				brand = "maestro";
				break;
			case /^(2131|1800|35)/.test(creditcardNumber):
				brand = "jcb";
				break;
			case /^(3[47])/.test(creditcardNumber):
				brand = "amex";
				break;
			case /^(5[1-5])/.test(creditcardNumber):
				brand = "mastercard";
				break;
			case /^(4)/.test(creditcardNumber):
				brand = "visa";
				break;
		}
	}

	return brand;
}

/**
 * Event Handler for the display of the card icons
 */
Paymill.prototype.paymillShowCardIcon = function()
{
	var brand = this.detectCreditcardBranding(pmQuery('#' + this.paymillCode + '_number').val());
	brand = brand.toLowerCase();
	pmQuery('#' + this.paymillCode + '_number')[0].className = pmQuery('#' + this.paymillCode + '_number')[0].className.replace(/paymill-card-number-.*/g, '');
	if (brand !== 'unknown') {
		if (brand === 'american express') {
			brand = 'amex';
		}

		pmQuery('#' + this.paymillCode + '_number').addClass("paymill-card-number-" + brand);
	}
}

Paymill.prototype.getValueIfExist = function(selector)
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
Paymill.prototype.paymillSubmitForm = function()
{
	PAYMILL_PUBLIC_KEY = pmQuery('.paymill-info-public_key-' + this.getPaymillCode()).val();
	this.paymillSelectedPaymentName = pmQuery("input[name='payment[method]']:checked").val();
	switch (this.paymillSelectedPaymentName) {
		case this.paymillCc:
			paymill.config('3ds_cancel_label', pmQuery('.paymill_3ds_cancel').val());
			if (pmQuery('.paymill-info-fastCheckout-cc').val() === 'false') {
				var valid = (paymill.validateCvc(pmQuery('#' + this.paymillSelectedPaymentName + '_cvc').val()) || paymill.cardType(pmQuery('#' + this.paymillSelectedPaymentName + '_number').val()).toLowerCase() === 'maestro')
						&& paymill.validateHolder(pmQuery('#' + this.paymillSelectedPaymentName + '_holdername').val())
						&& paymill.validateExpiry(pmQuery('#' + this.paymillSelectedPaymentName + '_expiry_month').val(), pmQuery('#' + this.paymillSelectedPaymentName + '_expiry_year').val())
						&& paymill.validateCardNumber(pmQuery('#' + this.paymillSelectedPaymentName + '_number').val());

				if (!valid) {
					return false;
				}

				var cvc = '000';

				if (pmQuery('#' + this.paymillSelectedPaymentName + '_cvc').val() !== '') {
					cvc = pmQuery('#' + this.paymillSelectedPaymentName + '_cvc').val();
				}

				this.debug("Generating Token");
				paymill.createToken({
					amount_int: parseInt(this.getTokenAmount()),
					currency: pmQuery('.paymill-payment-currency-' + this.getPaymillCode()).val(), // ISO 4217 e.g. "EUR"
					number: pmQuery('#' + this.paymillSelectedPaymentName + '_number').val(),
					exp_month: pmQuery('#' + this.paymillSelectedPaymentName + '_expiry_month').val(),
					exp_year: pmQuery('#' + this.paymillSelectedPaymentName + '_expiry_year').val(),
					cvc: cvc,
					cardholder: pmQuery('#' + this.paymillSelectedPaymentName + '_holdername').val()
				}, paymillResponseHandler);
			}
			break;
		case this.paymillElv:
			if (pmQuery('.paymill-info-fastCheckout-elv').val() === 'false') {
				if (pmQuery('.paymill-info-sepa-elv').val() === 'false') {
					var valid = pmQuery('#' + this.paymillSelectedPaymentName + '_holdername').val() !== ''
							&& paymill.validateAccountNumber(pmQuery('#' + this.paymillSelectedPaymentName + '_account').val())
							&& paymill.validateBankCode(pmQuery('#' + this.paymillSelectedPaymentName + '_bankcode').val());

					if (!valid) {
						return false;
					}

					this.debug("Generating Token");
					paymill.createToken({
						number: pmQuery('#' + this.paymillSelectedPaymentName + '_account').val(),
						bank: pmQuery('#' + this.paymillSelectedPaymentName + '_bankcode').val(),
						accountholder: pmQuery('#' + this.paymillSelectedPaymentName + '_holdername').val()
					}, paymillResponseHandler);
				} else {
					var valid = pmQuery('#' + this.paymillSelectedPaymentName + '_holdername').val() !== ''
							&& pmQuery('#' + this.paymillSelectedPaymentName + '_iban').val() !== ''
							&& pmQuery('#' + this.paymillSelectedPaymentName + '_bic').val() !== '';

					if (!valid) {
						return false;
					}

					this.debug("Generating Token");
					paymill.createToken({
						iban: pmQuery('#' + this.paymillSelectedPaymentName + '_iban').val(),
						bic: pmQuery('#' + this.paymillSelectedPaymentName + '_bic').val(),
						accountholder: pmQuery('#' + this.paymillSelectedPaymentName + '_holdername').val()
					}, paymillResponseHandler);
				}
			}

			break;
	}

	return false;
}

Paymill.prototype.logError = function(data)
{
	var that = this;
	pmQuery.ajax({
		async: false,
		type: "POST",
		url: pmQuery('.paymill-payment-token-log-' + this.getPaymillCode()).val(),
		data: {error: data},
	}).done(function(msg) {
		that.debug('Logging done.');
	}).fail(function(jqXHR, textStatus) {
		that.debug('Logging failed.');
	});
}

Paymill.prototype.getTokenAmount = function()
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

		nv['paymill-validate-' + this.getPaymillCode() + '-token'] = new Validator(
			'paymill-validate-' + this.getPaymillCode() + '-token',
			this.getValueIfExist('.paymill-payment-error-' + this.getPaymillCode() + '-token') + " Amount not accessable. Reason: " + textStatus,
			function(v) {
				return v !== '';
			},
			''
		);

		Object.extend(Validation.methods, nv);
	});

	return returnVal;
}

Paymill.prototype.unsetElvValidationRules = function()
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
			'',
			function(v) {
				return true;
			},
			''
		),
		'paymill-validate-dd-bic': new Validator(
			'paymill-validate-dd-bic',
			'',
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

Paymill.prototype.unsetCcValidationRules = function()
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

Paymill.prototype.setElvValidationRules = function()
{
	var nvElv = {
		'paymill-validate-dd-holdername': new Validator(
			'paymill-validate-dd-holdername',
			this.getValueIfExist('.paymill-payment-error-holder-elv'),
			function(v) {
				return !(v === '');
			},
			''
		),
		'paymill-validate-dd-iban': new Validator(
			'paymill-validate-dd-iban',
			this.getValueIfExist('.paymill-payment-error-iban-elv'),
			function(v) {
				return !(v === '');
			},
			''
		),
		'paymill-validate-dd-bic': new Validator(
			'paymill-validate-dd-bic',
			this.getValueIfExist('.paymill-payment-error-bic-elv'),
			function(v) {
				return !(v === '');
			},
			''
		),
		'paymill-validate-dd-account': new Validator(
			'paymill-validate-dd-account',
			this.getValueIfExist('.paymill-payment-error-number-elv'),
			function(v) {
				return paymill.validateAccountNumber(v);
			},
			''
		),
		'paymill-validate-dd-bankcode': new Validator(
			'paymill-validate-dd-bankcode',
			this.getValueIfExist('.paymill-payment-error-bankcode'),
			function(v) {
				return paymill.validateBankCode(v);
			},
			''
		)
	};

	Object.extend(Validation.methods, nvElv);
}

Paymill.prototype.setCcValidationRules = function()
{
	var nvCc = {
		'paymill-validate-cc-number': new Validator(
			'paymill-validate-cc-number',
			this.getValueIfExist('.paymill-payment-error-number'),
			function(v) {
				return paymill.validateCardNumber(v);
			},
			''
		),
		'paymill-validate-cc-expdate-month': new Validator(
			'paymill-validate-cc-expdate-month',
			this.getValueIfExist('.paymill-payment-error-expdate'),
			function(v) {

				return paymill.validateExpiry(v, pmQuery('.paymill-validate-cc-expdate-year').val());
			},
			''
		),
		'paymill-validate-cc-expdate-year': new Validator(
			'paymill-validate-cc-expdate-year',
			this.getValueIfExist('.paymill-payment-error-expdate'),
			function(v) {
				return paymill.validateExpiry(pmQuery('.paymill-validate-cc-expdate-month').val(), v);
			},
			''
		),
		'paymill-validate-cc-holder': new Validator(
			'paymill-validate-cc-holder',
			this.getValueIfExist('.paymill-payment-error-holder'),
			function(v) {
				return (paymill.validateHolder(v));
			},
			''
		),
		'paymill-validate-cc-cvc': new Validator(
			'paymill-validate-cc-cvc',
			this.getValueIfExist('.paymill-payment-error-cvc'),
			function(v) {
				if (paymill.cardType(pmQuery('#' + this.paymillCode + '_number').val()).toLowerCase() === 'maestro') {
					return true;
				}

				return paymill.validateCvc(v);
			},
			''
		)
	};

	Object.extend(Validation.methods, nvCc);
}

Paymill.prototype.addPaymillEvents = function()
{
	var that = this;
	
	this.setElvValidationRules();

	this.setCcValidationRules();

	if (pmQuery('.paymill-info-fastCheckout-elv').val() === 'true') {
		that.unsetElvValidationRules();
	}

	if (pmQuery('.paymill-info-fastCheckout-cc').val() === 'true') {
		that.unsetCcValidationRules();
	}

	pmQuery('#' + this.paymillCode + '_iban').keyup(function() {
		var iban = pmQuery('#' + that.paymillCode + '_iban').val();
		if (!iban.match(/^DE/)) {
			var newVal = "DE";
			if (iban.match(/^.{2}(.*)/)) {
				newVal += iban.match(/^.{2}(.*)/)[1];
			}

			pmQuery('#' + that.paymillCode + '_iban').val(newVal);
		}
	});

	pmQuery('#' + this.paymillCode + '_iban').trigger('keyup');

	if (!this.eventFlag) {
		
		pmQuery('#' + this.paymillCode + '_holdername').live('input', function() {
			that.setElvValidationRules();
			pmQuery('.paymill-info-fastCheckout-elv').val('false');
		});

		pmQuery('#' + this.paymillCode + '_account').live('input', function() {
			that.setElvValidationRules();
			pmQuery('.paymill-info-fastCheckout-elv').val('false');
		});

		pmQuery('#' + this.paymillCode + '_bankcode').live('input', function() {
			that.setElvValidationRules();
			pmQuery('.paymill-info-fastCheckout-elv').val('false');
		});
		
		pmQuery('#' + this.paymillCode + '_iban').live('input', function() {
			that.setElvValidationRules();
			pmQuery('.paymill-info-fastCheckout-elv').val('false');
		});
		
		pmQuery('#' + this.paymillCode + '_bic').live('input', function() {
			that.setElvValidationRules();
			pmQuery('.paymill-info-fastCheckout-elv').val('false');
		});

		pmQuery('#' + this.paymillCode + '_holdername').live('input', function() {
			that.setCcValidationRules();
			pmQuery('.paymill-info-fastCheckout-cc').val('false');
		});

		pmQuery('#' + this.paymillCode + '_cvc').live('input', function() {
			that.setCcValidationRules();
			pmQuery('.paymill-info-fastCheckout-cc').val('false');
		});

		pmQuery('#' + this.paymillCode + '_number').live('input', function() {
			that.setCcValidationRules();
			pmQuery('.paymill-info-fastCheckout-cc').val('false');
		});

		pmQuery('#' + this.paymillCode + '_expiry_month').live('change', function() {
			that.setCcValidationRules();
			pmQuery('.paymill-info-fastCheckout-cc').val('false');
		});

		pmQuery('#' + this.paymillCode + '_expiry_year').live('change', function() {
			that.setCcValidationRules();
			pmQuery('.paymill-info-fastCheckout-cc').val('false');
		});

		pmQuery('#' + this.paymillCode + '_number').live('input', function() {
			that.paymillShowCardIcon();
		});

		pmQuery('#' + this.paymillCode + '_cvc').live('input', function() {
			that.paymillSubmitForm();
		});

		pmQuery('#' + this.paymillCode + '_expiry_month').live('change', function() {
			that.paymillSubmitForm();
		});

		pmQuery('#' + this.paymillCode + '_expiry_year').live('change', function() {
			that.paymillSubmitForm();
		});

		pmQuery('#' + this.paymillCode + '_number').live('input', function() {
			that.paymillSubmitForm();
		});

		pmQuery('#' + this.paymillCode + '_holdername').live('input', function() {
			that.paymillSubmitForm();
		});

		pmQuery('#' + this.paymillCode + '_holdername').live('input', function() {
			that.paymillSubmitForm();
		});

		pmQuery('#' + this.paymillCode + '_account').live('input', function() {
			that.paymillSubmitForm();
		});

		pmQuery('#' + this.paymillCode + '_bankcode').live('input', function() {
			that.paymillSubmitForm();
		});

		pmQuery('#' + this.paymillCode + '_iban').live('input', function() {
			that.paymillSubmitForm();
		});

		pmQuery('#' + this.paymillCode + '_bic').live('input', function() {
			that.paymillSubmitForm();
		});

		this.eventFlag = true;
	}
}

/**
 * Handles the response of the paymill bridge. saves the token in a formfield.
 * @param {Boolean} error
 * @param {response object} result
 */
paymillResponseHandler = function(error, result)
{
	var nv = {};
	paymillObj = new Paymill();
	paymillObj.setCodes();
	if (error) {
		
		var message = 'unknown_error';
		var key = error.apierror;
		if(paymillObj.getValueIfExist('.PAYMILL_' + key + '-' + paymillObj.getPaymillCode()) !== ''){
			message = paymillObj.getValueIfExist('.PAYMILL_' + key + '-' + paymillObj.getPaymillCode());
		}
		
		if (message === 'unknown_error' && error.message !== undefined) {
			message = error.message;
		}
		
		// Appending error
		nv['paymill-validate-' + paymillObj.getPaymillCode() + '-token'] = new Validator(
			'paymill-validate-' + paymillObj.getPaymillCode() + '-token',
			paymillObj.getValueIfExist('.paymill-payment-error-' + paymillObj.getPaymillCode() + '-token') + message,
			function(v) {
				return false;
			},
			''
		);

		Object.extend(Validation.methods, nv);

		paymillObj.logError(error);

		paymillObj.debug(error.apierror);
		paymillObj.debug(error.message);
		paymillObj.debug("Paymill Response Handler triggered: Error.");
	} else {
		nv['paymill-validate-' + paymillObj.getPaymillCode() + '-token'] = new Validator(
			'paymill-validate-' + paymillObj.getPaymillCode() + '-token',
			'',
			function(v) {
				return true;
			},
			''
		);

		Object.extend(Validation.methods, nv);
		// Appending Token to form
		paymillObj.debug("Saving Token in Form: " + result.token);
		pmQuery('.paymill-payment-token-' + paymillObj.getPaymillCode()).val(result.token);
	}
}