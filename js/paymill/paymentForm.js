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
 * Get credit card brand
 *
 * @param String creditcardNumber
 * @returns String
 */
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
				if (pmQuery('.paymill-info-sepa-elv').val() === 'false') {
					var valid = pmQuery('#' + this.paymillSelectedPaymentName + '_holdername').val() !== ''
							&& paymill.validateAccountNumber(pmQuery('#' + this.paymillSelectedPaymentName + '_account').val())
							&& paymill.validateBankCode(pmQuery('#' + this.paymillSelectedPaymentName + '_bankcode').val());

					if (!valid) {
						return false;
					}

					window.PAYMILL_LOADING = true;
					this.debug("Generating Token");
					paymill.createToken({
						number: pmQuery('#' + this.paymillSelectedPaymentName + '_account').val(),
						bank: pmQuery('#' + this.paymillSelectedPaymentName + '_bankcode').val(),
						accountholder: pmQuery('#' + this.paymillSelectedPaymentName + '_holdername').val()
					}, paymillResponseHandler);
				} else {
                                        ibanWithoutSpaces = pmQuery('#' + this.paymillSelectedPaymentName + '_iban').val();
                                        ibanWithoutSpaces = ibanWithoutSpaces.replace(/\s+/g, "");
                                        ibanValidator = new PaymillIban();
					var valid = pmQuery('#' + this.paymillSelectedPaymentName + '_holdername').val() !== ''
							&& ibanValidator.validate(pmQuery('#' + this.paymillSelectedPaymentName + '_iban').val())
							&& (pmQuery('#' + this.paymillSelectedPaymentName + '_bic').val().length === 8
                                                            || pmQuery('#' + this.paymillSelectedPaymentName + '_bic').val().length === 11);

					if (!valid) {
						return false;
					}

					window.PAYMILL_LOADING = true;
					this.debug("Generating Token");
					paymill.createToken({
						iban: ibanWithoutSpaces,
						bic: pmQuery('#' + this.paymillSelectedPaymentName + '_bic').val(),
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
		'paymill-validate-dd-iban': new Validator(
			'paymill-validate-dd-iban',
			this.getValueIfExist('.paymill-payment-error-iban-elv'),
			function(v) {
                                iban = new PaymillIban();
				return iban.validate(v);
			},
			''
		),
		'paymill-validate-dd-bic': new Validator(
			'paymill-validate-dd-bic',
			this.getValueIfExist('.paymill-payment-error-bic-elv'),
			function(v) {
				return v.length === 8 || v.length === 11;
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
	window.PAYMILL_LOADING = false;

	var nv = {};
	paymillObj = new Paymill();
	paymillObj.setCodes();
	if (error) {
		pmQuery('#paymill_creditcard_cvc').val('');
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

PaymillIban = function() {
};
PaymillIban.prototype.countries = {
    'AL': 28,
    'AD': 24,
    'AZ': 28,
    'BH': 22,
    'BE': 16,
    'BA': 20,
    'BR': 29,
    'BG': 22,
    'CR': 21,
    'DK': 18,
    'DE': 22,
    'DO': 28,
    'EE': 20,
    'FO': 18,
    'FI': 18,
    'FR': 27,
    'GF': 27,
    'PF': 27,
    'TF': 27,
    'GE': 22,
    'GI': 23,
    'GR': 27,
    'GL': 18,
    'GP': 27,
    'GT': 28,
    'HK': 16,
    'IE': 22,
    'IS': 26,
    'IL': 23,
    'IT': 27,
    'JO': 30,
    'VG': 24,
    'KZ': 20,
    'QA': 29,
    'HR': 21,
    'KW': 30,
    'LV': 21,
    'LB': 28,
    'LI': 21,
    'LT': 20,
    'LU': 20,
    'MT': 31,
    'MA': 24,
    'MQ': 27,
    'MR': 27,
    'MU': 30,
    'YT': 27,
    'MK': 19,
    'MD': 24,
    'MC': 27,
    'ME': 22,
    'NC': 27,
    'NL': 18,
    'NO': 15,
    'AT': 20,
    'PK': 24,
    'PS': 29,
    'PL': 28,
    'PT': 25,
    'RE': 27,
    'RO': 24,
    'BL': 27,
    'MF': 27,
    'SM': 27,
    'SA': 24,
    'SE': 24,
    'CH': 21,
    'RS': 22,
    'SK': 24,
    'SI': 19,
    'ES': 24,
    'PM': 27,
    'CZ': 24,
    'TN': 24,
    'TR': 26,
    'HU': 28,
    'AE': 23,
    'GB': 22,
    'WF': 27,
    'CY': 28
};

PaymillIban.prototype.alphabet = {
    'A': '10',
    'B': '11',
    'C': '12',
    'D': '13',
    'E': '14',
    'F': '15',
    'G': '16',
    'H': '17',
    'I': '18',
    'J': '19',
    'K': '20',
    'L': '21',
    'M': '22',
    'N': '23',
    'O': '24',
    'P': '25',
    'Q': '26',
    'R': '27',
    'S': '28',
    'T': '29',
    'U': '30',
    'V': '31',
    'W': '32',
    'X': '33',
    'Y': '34',
    'Z': '35'
};

PaymillIban.prototype.iban = '';

PaymillIban.prototype.validate = function(iban) {
    if (iban === undefined || iban === "") {
        return false;
    }

    //removing spaces
    iban = iban.replace(/\s+/g, "");

    this.iban = iban;

    if (!this.checkString) {
        return false;
    }

    if (!this.checkLength()) {
        return false;
    }

    this.changeCharacterPosition(iban);
    this.replaceLettersWithNumbers();

    ibanWithoutCheckDigits = this.iban.substr(0, this.iban.length - 2);
    ibanWithZeroCheckDigits = ibanWithoutCheckDigits + "00";
    ibanCheckDigits = this.iban.substr(this.iban.length - 2, 2);

    calcCheckDigits = (98 - this.calculate(ibanWithZeroCheckDigits)).toString();

    if (calcCheckDigits.length === 1) {
        calcCheckDigits = '0' + calcCheckDigits;
    }

    if (calcCheckDigits !== ibanCheckDigits) {
        return false;
    }

    if (this.calculate(this.iban) !== '01') {
        return false;
    }

    return true;
};

//checking if the given IBAN is alphanumeric, the first two positions are letters and the next two positions numbers
PaymillIban.prototype.checkString = function() {
    return this.iban.match(/^[a-z]{2}[0-9]{2}[a-z0-9]+$/i) !== null;
};


//checking if the given IBAN has the right length based on the first two letters which have to be a country code
PaymillIban.prototype.checkLength = function() {
    countryCode = this.iban.substr(0, 2);
    return this.countries[countryCode] === this.iban.length;
};

//Replace Letters with Numbers A = 10,....,Z = 35
PaymillIban.prototype.replaceLettersWithNumbers = function() {
    for (character in this.alphabet) {
        regex = new RegExp(character, 'g');
        this.iban = this.iban.replace(regex, this.alphabet[character]);
    }
};

//Puts the first 4 Characters to the End
PaymillIban.prototype.changeCharacterPosition = function() {
    firstFourCharacters = this.iban.substr(0, 4);
    leftOverString = this.iban.substr(4);
    this.iban = leftOverString + firstFourCharacters;
};

//calculate the IBAN Hash with piece-wise manner modulo operations, since javascript cant handle 128 bit integer
PaymillIban.prototype.calculate = function(iban) {
    start = 0;
    length = 9;
    loop = true;
    remainder = '';
    while (loop) {
        if (iban.substr(start, length).length < 7) {
            loop = false;
            length = iban.substr(start, length).length;
        }
        tempInt = remainder + iban.substr(start, length);
        remainder = (tempInt % 97) + "";
        if (remainder.length === 1) {
            remainder = '0' + remainder;
        }
        start = start + length;
        length = 7;
    }
    return remainder;
};