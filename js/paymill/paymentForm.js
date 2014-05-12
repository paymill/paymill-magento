var PAYMILL_PUBLIC_KEY = null;

/**
 * Build object
 * @returns {Paymill}
 */
function Paymill()
{
	this.paymillSelectedPaymentName = "Preparing Payment";
	this.eventFlag = false;

}

/**
 * Set payment code
 * @param String code
 */
Paymill.prototype.setPaymillCode = function(code)
{
	this.paymillCode = code;
}

/**
 * Set the possible payment codes
 */
Paymill.prototype.setCodes = function()
{
	this.paymillCc = 'paymill_creditcard';
	this.paymillElv = 'paymill_directdebit';
}

/**
 * Get selected payments short code
 * @returns String
 */
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

/**
 * Event Handler for the display of the card icons
 */
Paymill.prototype.paymillShowCardIcon = function()
{
    var detector = new PaymillBrandDetection();
    var brand = detector.detect(pmQuery('#' + this.paymillCode + '_number').val());
	brand = brand.toLowerCase();
	pmQuery('#' + this.paymillCode + '_number')[0].className = pmQuery('#' + this.paymillCode + '_number')[0].className.replace(/paymill-card-number-.*/g, '');
	if (brand !== 'unknown') {
        if(this.creditcards.length > 0 && pmQuery.inArray(brand, this.creditcards) === -1) {
            return;
        }
		pmQuery('#' + this.paymillCode + '_number').addClass("paymill-card-number-" + brand);
        if (!detector.validate(pmQuery('#' + this.paymillCode + '_number').val())) {
            pmQuery('#' + this.paymillCode + '_number').addClass("paymill-card-number-grayscale");
        }
	}
}

/**
 * Prototype selector
 *
 * @param {type} selector
 * @returns {String}
 */
Paymill.prototype.getValueIfExist = function(selector)
{
	if ($$(selector)[0]) {
		return $$(selector)[0].value;
	}

	return '';
}

/**
 * Validate the form data and try to create a token
 *
 * @returns {Boolean}
 */
Paymill.prototype.paymillSubmitForm = function()
{
	PAYMILL_PUBLIC_KEY = pmQuery('.paymill-info-public_key-' + this.getPaymillCode()).val();
	this.paymillSelectedPaymentName = pmQuery("input[name='payment[method]']:checked").val();

	if (!window.PAYMILL_LOADING !== "undefined" && window.PAYMILL_LOADING) {
		return false;
	}

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

				window.PAYMILL_LOADING = true;
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
				if (!this.isSepa()) {
					var valid = pmQuery('#' + this.paymillSelectedPaymentName + '_holdername').val() !== ''
							&& paymill.validateAccountNumber(pmQuery('#' + this.paymillSelectedPaymentName + '_account_iban').val())
							&& paymill.validateBankCode(pmQuery('#' + this.paymillSelectedPaymentName + '_bankcode_bic').val());

					if (!valid) {
						return false;
					}

					window.PAYMILL_LOADING = true;
					this.debug("Generating Token");
					paymill.createToken({
						number: pmQuery('#' + this.paymillSelectedPaymentName + '_account_iban').val(),
						bank: pmQuery('#' + this.paymillSelectedPaymentName + '_bankcode_bic').val(),
						accountholder: pmQuery('#' + this.paymillSelectedPaymentName + '_holdername').val()
					}, paymillResponseHandler);
				} else {
                    ibanWithoutSpaces = pmQuery('#' + this.paymillSelectedPaymentName + '_account_iban').val();
                    ibanWithoutSpaces = ibanWithoutSpaces.replace(/\s+/g, "");
                    ibanValidator = new PaymillIban();
					var valid = pmQuery('#' + this.paymillSelectedPaymentName + '_holdername').val() !== ''
							&& ibanValidator.validate(pmQuery('#' + this.paymillSelectedPaymentName + '_account_iban').val())
							&& (pmQuery('#' + this.paymillSelectedPaymentName + '_bankcode_bic').val().length === 8
                                                            || pmQuery('#' + this.paymillSelectedPaymentName + '_bankcode_bic').val().length === 11);

					if (!valid) {
						return false;
					}

					window.PAYMILL_LOADING = true;
					this.debug("Generating Token");
					paymill.createToken({
						iban: ibanWithoutSpaces,
						bic: pmQuery('#' + this.paymillSelectedPaymentName + '_bankcode_bic').val(),
						accountholder: pmQuery('#' + this.paymillSelectedPaymentName + '_holdername').val()
					}, paymillResponseHandler);
				}
			}

			break;
	}

	return false;
}

/**
 * Log data
 * @param String data
 */
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

/**
 * Return order amount
 * @return float
 */
Paymill.prototype.getTokenAmount = function()
{
	var that = this;
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

		nv['paymill-validate-' + that.getPaymillCode() + '-token'] = new Validator(
			'paymill-validate-' + that.getPaymillCode() + '-token',
			that.getValueIfExist('.paymill-payment-error-' + that.getPaymillCode() + '-token') + " Amount not accessable. Reason: " + textStatus,
			function(v) {
				return v !== '';
			},
			''
		);

		Object.extend(Validation.methods, nv);
	});

	return returnVal;
}

/**
 * Unset elv validation rules
 */
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
		'paymill-validate-dd-account-iban': new Validator(
			'paymill-validate-dd-account-iban',
			'',
			function(v) {
				return true;
			},
			''
		),
		'paymill-validate-dd-bankcode-bic': new Validator(
			'paymill-validate-dd-bankcode-bic',
			'',
			function(v) {
				return true;
			},
			''
		)
	};

	Object.extend(Validation.methods, nvElv);
}

/**
 * Unset cc validation rules
 */
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

/**
 * Set elv validation rules
 */
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
		'paymill-validate-dd-account-iban': new Validator(
			'paymill-validate-dd-account-iban',
			this.getValueIfExist('.paymill-payment-error-number-iban-elv'),
			function(v) {
                if(paymillElv.isSepa()) {
                    iban = new PaymillIban();
                    return iban.validate(v);
                }
                return paymill.validateAccountNumber(v);
			},
			''
		),
		'paymill-validate-dd-bankcode-bic': new Validator(
			'paymill-validate-dd-bankcode-bic',
			this.getValueIfExist('.paymill-payment-error-bankcode-bic-elv'),
			function(v) {
                if(paymillElv.isSepa()) {
                    return v.length === 8 || v.length === 11;
                }
                return paymill.validateBankCode(v);
			},
			''
		)
	};

	Object.extend(Validation.methods, nvElv);
}

/**
 * Set cc validation rules
 */
Paymill.prototype.setCcValidationRules = function()
{
	var that = this;
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
				if (paymill.cardType(pmQuery('#' + that.paymillCode + '_number').val()).toLowerCase() === 'maestro') {
					return true;
				}

				return paymill.validateCvc(v);
			},
			''
		)
	};

	Object.extend(Validation.methods, nvCc);
}

/**
 * Add all paymill events
 */
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

	if (!this.eventFlag) {

		pmQuery('#' + this.paymillCode + '_holdername').live('input', function() {
			that.setElvValidationRules();
			pmQuery('.paymill-info-fastCheckout-elv').val('false');
		});

		pmQuery('#' + this.paymillCode + '_account_iban').live('input', function() {
			that.setElvValidationRules();
			pmQuery('.paymill-info-fastCheckout-elv').val('false');
		});

		pmQuery('#' + this.paymillCode + '_bankcode_bic').live('input', function() {
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

		pmQuery('#' + this.paymillCode + '_account_iban').live('input', function() {
			that.paymillSubmitForm();
		});

		pmQuery('#' + this.paymillCode + '_bankcode_bic').live('input', function() {
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
	window.PAYMILL_LOADING = false;

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
        var paymillValidator = new Validation(pmQuery('#paymill_creditcard_cvc').get(0).form.id);
        paymillValidator.validate();
        pmQuery('#paymill_creditcard_cvc').val('');
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

/**
 *  If array is empty, all creditcards are shown in Brand Detection
 */
Paymill.prototype.setBrandCreditcards = function(creditcards)
{
    this.creditcards = creditcards;
}

/**
 * If value starts with 2 letters, it will be considered as sepa
 */
Paymill.prototype.isSepa = function()
{
    var reg = new RegExp(/^\D{2}/);
    return reg.test(pmQuery('#' + this.paymillCode + '_account_iban').val());
}