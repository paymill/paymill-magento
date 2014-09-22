function Creditcard()
{
    this.helper = new PaymillHelper();
}

Creditcard.prototype.setValidationRules = function ()
{
    var that = this;
    
    Object.extend(Validation.methods, {
        'paymill-validate-cc-number': new Validator(
            'paymill-validate-cc-number',
            this.helper.getElementValue('.paymill-payment-error-number'),
            function(v) {
                return paymill.validateCardNumber(v);
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
}

Creditcard.prototype.getTokenParameter = function()
{
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
        cardholder: this.helper.getElementValue('#paymill_creditcard_holdername')
    };
}

Paymill.prototype.getTokenAmount = function()
{
    var that = this;
    var returnVal = null;
    
    new Ajax.Request(this.helper.getElementValue('.paymill-payment-token-url-cc'), {
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
            )});
        }

    });

    return returnVal;
}