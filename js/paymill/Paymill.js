var PAYMILL_PUBLIC_KEY = null;
var paymillButton = false;

function Paymill(methodCode)
{
    this.methodInstance = null;
    this.methodCode = methodCode;
    if (methodCode === 'paymill_creditcard') {
        this.methodInstance = new Creditcard();
    }
    
    if (methodCode === 'paymill_directdebit') {
        this.methodInstance = new Elv();
    }
    
    this.helper = new PaymillHelper();
}

Paymill.prototype.validate = function()
{
    this.debug("Start form validation");
    var valid = this.methodInstance.validate();
    this.debug(valid);
    return valid;
};

Paymill.prototype.generateToken = function()
{
    if (this.validate()) {
        var data = this.methodInstance.getTokenParameter();
        this.debug("Generating Token");
        this.debug(data);
        paymill.createToken(
            data, 
            tokenCallback
        );
    }
};

Paymill.prototype.generateTokenOnSubmit = function()
{
    if (this.helper.getElementValue('.paymill-info-fastCheckout-' + this.helper.getShortCode()) === 'true') {
        payment.save();
    } else {
        this.generateToken();
    }
};

Paymill.prototype.setValidationRules = function()
{
    this.methodInstance.setValidationRules();
};

Paymill.prototype.logError = function(data)
{
    var that = this;
    new Ajax.Request(this.helper.getElementValue('.paymill-payment-token-log-' + this.helper.getShortCode()), {
      method: 'post',
      parameters: data,
      onSuccess: function(response) {
          that.debug('Logging done.');
      }, onFailure: function() {
          that.debug('Logging failed.');
      }
    });
};

Paymill.prototype.debug = function(message)
{
    if (this.helper.getElementValue('.paymill-option-debug-' + this.helper.getShortCode()) === "1") {
        console.log(message);
    }
};

Paymill.prototype.setEventListener = function(selector)
{
    this.methodInstance.setEventListener(selector);
};

Paymill.prototype.setCreditcards = function(creditcards)
{
    this.methodInstance.creditcards = creditcards;
};

tokenCallback = function(error, result)
{
    var paymill = new Paymill('default');

    paymill.debug("Enter paymillResponseHandler");

    var rules = {};
    if (error) {
        var message = 'unknown_error';
        var key = error.apierror;
        if (paymill.helper.getElementValue('.PAYMILL_' + key + '-' + paymill.helper.getShortCode()) !== '') {
            message = paymill.helper.getElementValue('.PAYMILL_' + key + '-' + paymill.helper.getShortCode());
        }

        if (message === 'unknown_error' && error.message !== undefined) {
            message = error.message;
        }

        // Appending error
        rules['paymill-validate-' + paymill.helper.getShortCode() + '-token'] = new Validator(
            'paymill-validate-' + paymill.helper.getShortCode() + '-token',
            paymill.helper.getElementValue('.paymill-payment-error-' + paymill.helper.getShortCode() + '-token') + ' ' + message,
            function(value) {
                return false;
            },
            ''
        );
        
        paymill.helper.setElementValue('#paymill_creditcard_cvc', '');
        paymill.logError(error);
        paymill.debug(error.apierror);
        paymill.debug(error.message);
        paymill.debug("Paymill Response Handler triggered: Error.");
        Object.extend(Validation.methods, rules);
        new Validation($$('#paymill_creditcard_cvc')[0].form.id).validate();
    } else {
        rules['paymill-validate-' + paymill.helper.getShortCode() + '-token'] = new Validator(
            'paymill-validate-' + paymill.helper.getShortCode() + '-token',
            '',
            function(value) {
                return true;
            },
            ''
        );

        Object.extend(Validation.methods, rules);

        paymill.debug("Saving Token in Form: " + result.token);
        paymill.helper.setElementValue('.paymill-payment-token-' + paymill.helper.getShortCode(), result.token);
        if (paymillButton) {
            payment.save();
        } else {
            new Validation($$('#paymill_creditcard_cvc')[0].form.id).validate();
        }
    }
    
    
    
}