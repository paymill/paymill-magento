function Elv()
{
    
}

Elv.prototype.setValidationRules = function ()
{
    Object.extend(Validation.methods, {
        'paymill-validate-dd-holdername': new Validator(
            'paymill-validate-dd-holdername',
            this.getValueIfExist('.paymill-payment-error-holder-elv'),
            function(v) {
                return !(v === '');
            },
            ''
        ), 'paymill-validate-dd-account-iban': new Validator(
            'paymill-validate-dd-account-iban',
            this.getValueIfExist('.paymill-payment-error-number-iban-elv'),
            function(v) {
                if (paymillElv.isSepa()) {
                    iban = new PaymillIban();
                    return iban.validate(v);
                }
                return paymill.validateAccountNumber(v);
            },
            ''
        ), 'paymill-validate-dd-bankcode-bic': new Validator(
            'paymill-validate-dd-bankcode-bic',
            this.getValueIfExist('.paymill-payment-error-bankcode-bic-elv'),
            function(v) {
                if (paymillElv.isSepa()) {
                    return v.length === 8 || v.length === 11;
                }
                return paymill.validateBankCode(v);
            },
            ''
        )
    });
}