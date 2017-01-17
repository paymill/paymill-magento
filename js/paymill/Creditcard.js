function Creditcard()
{
    this.helper = new PaymillHelper();
}

Creditcard.prototype.validate = function()
{
    var valid = true;
    if (this.helper.getElementValue('.paymill-info-fastCheckout-cc') === 'false') {
        if (!paymill.validateCvc(this.helper.getElementValue('#paymill_creditcard_cvc'))) {
            if (paymill.cardType(this.helper.getElementValue('#paymill_creditcard_number')).toLowerCase() !== 'maestro') {
                valid = false;
            }
        }

        if (!paymill.validateHolder(this.helper.getElementValue('#paymill_creditcard_holdername'))) {
            valid = false;
        }

        if (!paymill.validateExpiry(
                this.helper.getElementValue('#paymill_creditcard_expiry_month'),
                this.helper.getElementValue('#paymill_creditcard_expiry_year'))) {
            valid = false;
        }

        if (!paymill.validateCardNumber(this.helper.getElementValue('#paymill_creditcard_number'))) {
            valid = false;
        }
    }

    return valid;
};

Creditcard.prototype.setValidationRules = function()
{
    var that = this;

    Object.extend(Validation.methods, {
        'paymill-validate-cc-number': new Validator(
            'paymill-validate-cc-number',
            this.helper.getElementValue('.paymill-payment-error-number'),
            function(value) {
                return paymill.validateCardNumber(value);
            },
            ''
        ), 'paymill-validate-cc-expdate-month': new Validator(
            'paymill-validate-cc-expdate-month',
            this.helper.getElementValue('.paymill-payment-error-expdate'),
            function(value) {
                return paymill.validateExpiry(value, that.helper.getElementValue('.paymill-validate-cc-expdate-year'));
            },
            ''
        ), 'paymill-validate-cc-expdate-year': new Validator(
                'paymill-validate-cc-expdate-year',
                this.helper.getElementValue('.paymill-payment-error-expdate'),
                function(value) {
                    return paymill.validateExpiry(that.helper.getElementValue('.paymill-validate-cc-expdate-month'), value);
                },
                ''
        ), 'paymill-validate-cc-holder': new Validator(
            'paymill-validate-cc-holder',
            this.helper.getElementValue('.paymill-payment-error-holder'),
            function(value) {
                return (paymill.validateHolder(value));
            },
            ''
        ), 'paymill-validate-cc-cvc': new Validator(
            'paymill-validate-cc-cvc',
            this.helper.getElementValue('.paymill-payment-error-cvc'),
            function(value) {
                if (paymill.cardType(that.helper.getElementValue('#paymill_creditcard_number')).toLowerCase() === 'maestro') {
                    return true;
                }

                return paymill.validateCvc(value);
            },
            ''
        )
    });
};

Creditcard.prototype.unsetValidationRules = function()
{
    Object.extend(Validation.methods, {
        'paymill-validate-cc-number': new Validator(
            'paymill-validate-cc-number',
            '',
            function(value) {
                return true;
            },
            ''
        ), 'paymill-validate-cc-expdate-month': new Validator(
            'paymill-validate-cc-expdate-month',
            '',
            function(value) {
                return true;
            },
            ''
        ), 'paymill-validate-cc-expdate-year': new Validator(
            'paymill-validate-cc-expdate-year',
            '',
            function(value) {
                return true;
            },
            ''
        ), 'paymill-validate-cc-holder': new Validator(
            'paymill-validate-cc-holder',
            '',
            function(value) {
                return true;
            },
            ''
        ), 'paymill-validate-cc-cvc': new Validator(
            'paymill-validate-cc-cvc',
            '',
            function(value) {
                return true;
            },
            ''
        )
    });
};

Creditcard.prototype.getTokenParameter = function()
{
    PAYMILL_PUBLIC_KEY = this.helper.getElementValue('.paymill-info-public_key-cc');
    paymill.config('3ds_cancel_label', this.helper.getElementValue('.paymill_3ds_cancel'));

    var cvc = '000';

    if (this.helper.getElementValue('#paymill_creditcard_cvc') !== '') {
        cvc = this.helper.getElementValue('#paymill_creditcard_cvc');
    }

    return {
        amount_int: parseInt(this.getTokenAmount()),
        currency: this.helper.getElementValue('.paymill-payment-currency-cc'),
        number: this.helper.getElementValue('#paymill_creditcard_number'),
        exp_month: this.helper.getElementValue('#paymill_creditcard_expiry_month'),
        exp_year: this.helper.getElementValue('#paymill_creditcard_expiry_year'),
        cvc: cvc,
        cardholder: this.helper.getElementValue('#paymill_creditcard_holdername'),
        email: this.helper.getElementValue('.paymill-payment-customer-email-cc')
    };
};

Creditcard.prototype.getFrameTokenParameter = function()
{
    PAYMILL_PUBLIC_KEY = this.helper.getElementValue('.paymill-info-public_key-cc');

    return {
        amount_int: parseInt(this.getTokenAmount()),
        currency: this.helper.getElementValue('.paymill-payment-currency-cc'),
        email: this.helper.getElementValue('.paymill-payment-customer-email-cc')
    };
};


Creditcard.prototype.getTokenAmount = function()
{
    var that = this;
    var returnVal = null;

    new Ajax.Request(this.helper.getElementValue('.paymill-payment-token-url-cc'), {
        asynchronous: false,
        onSuccess: function(response) {
            returnVal = response.transport.responseText;
        }, onFailure: function() {
            Object.extend(Validation.methods, {
                'paymill-validate-cc-token': new Validator(
                    'paymill-validate-cc-token',
                    that.helper.getElementValue('.paymill-payment-error-cc-token') + " Amount not accessable.",
                    function(value) {
                        return value !== '';
                    },
                ''
                )
            });
        }

    });

    return returnVal;
};

Creditcard.prototype.paymillShowCardIcon = function()
{
    var detector = new PaymillBrandDetection();
    var brand = detector.detect(this.helper.getElementValue('#paymill_creditcard_number'));
    brand = brand.toLowerCase();
    $$('#paymill_creditcard_number')[0].className = $$('#paymill_creditcard_number')[0].className.replace(/paymill-card-number-.*/g, '');
    if (brand !== 'unknown') {
        if(this.creditcards.length > 0 && this.creditcards.indexOf(brand) === -1) {
            return;
        }

        $$('#paymill_creditcard_number')[0].addClassName("paymill-card-number-" + brand);
        if (!detector.validate(this.helper.getElementValue('#paymill_creditcard_number'))) {
            $$('#paymill_creditcard_number')[0].addClassName("paymill-card-number-grayscale");
        }
    }
};

Creditcard.prototype.setEventListener = function(selector)
{
    var that = this;

    if (this.helper.getElementValue('.paymill-info-fastCheckout-cc') === 'true') {
        that.unsetValidationRules();
    }

    Event.observe('paymill_creditcard_number','keyup', function() {
        that.setValidationRules();
        that.helper.setElementValue('.paymill-info-fastCheckout-cc', 'false');
        if (!$$(selector)[0]) {
            paymillCreditcard.generateToken();
        } else {
            paymillCreditcard.setOnClickHandler(selector);
        }
    });

    Event.observe('paymill_creditcard_cvc', 'keyup', function() {
        that.setValidationRules();
        that.helper.setElementValue('.paymill-info-fastCheckout-cc', 'false');
        if (!$$(selector)[0]) {
            paymillCreditcard.generateToken();
        } else {
            paymillCreditcard.setOnClickHandler(selector);
        }
    });

    Event.observe('paymill_creditcard_expiry_month', 'change', function() {
        that.setValidationRules();
        that.helper.setElementValue('.paymill-info-fastCheckout-cc', 'false');
        if (!$$(selector)[0]) {
            paymillCreditcard.generateToken();
        } else {
            paymillCreditcard.setOnClickHandler(selector);
        }
    });

    Event.observe('paymill_creditcard_expiry_year', 'change', function() {
        that.setValidationRules();
        that.helper.setElementValue('.paymill-info-fastCheckout-cc', 'false');
        if (!$$(selector)[0]) {
            paymillCreditcard.generateToken();
        } else {
            paymillCreditcard.setOnClickHandler(selector);
        }
    });

    Event.observe('paymill_creditcard_holdername', 'keyup', function() {
        that.setValidationRules();
        that.helper.setElementValue('.paymill-info-fastCheckout-cc', 'false');
        if (!$$(selector)[0]) {
            paymillCreditcard.generateToken();
        } else {
            paymillCreditcard.setOnClickHandler(selector);
        }
    });

    Event.observe('paymill_creditcard_number', 'keyup', function() {
        that.paymillShowCardIcon();
    });

};

Creditcard.prototype.setCreditcards = function(creditcards)
{
    this.creditcards = creditcards;
};

Creditcard.prototype.openPaymillFrame = function(lang)
{
    $$('#paymillFastCheckoutDiv')[0].parentNode.removeChild($$('#paymillFastCheckoutDiv')[0]);
    paymill.embedFrame('paymillContainer', {lang: lang}, PaymillFrameResponseHandler);
    this.helper.setElementValue('.paymill-info-fastCheckout-cc', 'false');
};

PaymillFrameResponseHandler = function(error)
{
    if (error) {
        paymillCreditcard.debug("iFrame load failed with " + error.apierror + error.message);
    } else {
        paymillCreditcard.debug("iFrame successfully loaded");
        paymillCreditcard.setOnClickHandler(paymillTokenSelector);
    }
}
