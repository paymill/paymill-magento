function Elv()
{
    this.helper = new PaymillHelper();
}

Elv.prototype.validate = function()
{
    var valid = true;
    
    if (this.helper.getElementValue('.paymill-info-fastCheckout-elv') === 'false') {
        if (this.helper.getElementValue('#paymill_directdebit_holdername') === '') {
            valid = false;
        }

        if (this.isSepa()) {
            ibanValidator = new PaymillIban();

            if (!ibanValidator.validate(this.helper.getElementValue('#paymill_directdebit_account_iban'))) {
                valid = false;
            }

            if (this.helper.getElementValue('#paymill_directdebit_bankcode_bic').length !== 8 
                    || this.helper.getElementValue('#paymill_directdebit_bankcode_bic').length !== 11) {
                valid = false;
            }
        } else {
            if (!paymill.validateAccountNumber(this.helper.getElementValue('#paymill_directdebit_account_iban'))) {
                valid = false;
            }

            if (!paymill.validateBankCode(this.helper.getElementValue('#paymill_directdebit_bankcode_bic'))) {
                valid = false;
            }
        }
    }
            
    return valid;
};

Elv.prototype.unsetValidationRules = function()
{
    Object.extend(Validation.methods, {
        'paymill-validate-dd-holdername': new Validator(
            'paymill-validate-dd-holdername',
            '',
            function(value) {
                return true;
            },
            ''
        ), 'paymill-validate-dd-account-iban': new Validator(
            'paymill-validate-dd-account-iban',
            '',
            function(value) {
                return true;
            },
            ''
        ), 'paymill-validate-dd-bankcode-bic': new Validator(
            'paymill-validate-dd-bankcode-bic',
            '',
            function(value) {
                return true;
            },
            ''
        )
    });
};

Elv.prototype.setValidationRules = function ()
{
    var that = this;
    Object.extend(Validation.methods, {
        'paymill-validate-dd-holdername': new Validator(
            'paymill-validate-dd-holdername',
            this.helper.getElementValue('.paymill-payment-error-holder-elv'),
            function(value) {
                return !(value === '');
            },
            ''
        ), 'paymill-validate-dd-account-iban': new Validator(
            'paymill-validate-dd-account-iban',
            this.helper.getElementValue('.paymill-payment-error-number-iban-elv'),
            function(value) {
                if (that.isSepa()) {
                    iban = new PaymillIban();
                    return iban.validate(value);
                }
                return paymill.validateAccountNumber(value);
            },
            ''
        ), 'paymill-validate-dd-bankcode-bic': new Validator(
            'paymill-validate-dd-bankcode-bic',
            this.helper.getElementValue('.paymill-payment-error-bankcode-bic-elv'),
            function(value) {
                if (that.isSepa()) {
                    return value.length === 8 || value.length === 11;
                }
                
                return paymill.validateBankCode(value);
            },
            ''
        )
    });
};

Elv.prototype.getTokenParameter = function()
{
    PAYMILL_PUBLIC_KEY = this.helper.getElementValue('.paymill-info-public_key-elv');
    
    var data = null;
    
    if (!this.isSepa()) {
        data = {
            number: this.helper.getElementValue('#paymill_directdebit_account_iban'),
            bank: this.helper.getElementValue('#paymill_directdebit_bankcode_bic'),
            accountholder: this.helper.getElementValue('#paymill_directdebit_holdername')
        };
    } else {
        data = {
            iban: this.helper.getElementValue('#paymill_directdebit_account_iban').replace(/\s+/g, ''),
            bic: this.helper.getElementValue('#paymill_directdebit_bankcode_bic'),
            accountholder: this.helper.getElementValue('#paymill_directdebit_holdername')
        };
    }
    
    return data;
}

Elv.prototype.isSepa = function()
{
    var reg = new RegExp(/^\D{2}/);
    return reg.test(this.helper.getElementValue('#paymill_directdebit_account_iban'));
};

Elv.prototype.setEventListener = function()
{
    var that = this;
    
    if (this.helper.getElementValue('.paymill-info-fastCheckout-elv') === 'true') {
        that.unsetValidationRules();
    }
    
    Event.observe('paymill_directdebit_holdername', 'keyup', function() {
        that.setValidationRules();
        that.helper.setElementValue('.paymill-info-fastCheckout-elv', 'false');
        paymillElv.generateToken();
    });

    Event.observe('paymill_directdebit_account_iban', 'keyup', function() {
        that.setValidationRules();
        that.helper.setElementValue('.paymill-info-fastCheckout-elv', 'false');
        paymillElv.generateToken();
    });

    Event.observe('paymill_directdebit_bankcode_bic', 'keyup', function() {
        that.setValidationRules();
        that.helper.setElementValue('.paymill-info-fastCheckout-elv', 'false');
        paymillElv.generateToken();
    });
};