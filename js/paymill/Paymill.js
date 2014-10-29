if (typeof Array.prototype.forEach !== 'function') {
    Array.prototype.forEach = function (callback, context) {
        for (var i = 0; i < this.length; i++) {
            callback.apply(context, [this[i], i, this]);
        }
    };
}

var PAYMILL_PUBLIC_KEY = null;
var paymillButton = false;
var onClickContent = false;
var onClickBounded = false;

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
		if (this.helper.getMethodCode() === 'paymill_creditcard') {
			new Validation($$('#paymill_creditcard_cvc')[0].form.id).validate();
		}
		
		if (this.helper.getMethodCode() === 'paymill_directdebit') {
			new Validation($$('#paymill_directdebit_holdername')[0].form.id).validate();
		}
		
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
    if (this.helper.getElementValue('.paymill-info-fastCheckout-' + this.helper.getShortCode()) !== 'true') {
		if (this.helper.getMethodCode() === 'paymill_creditcard') {
			if (new Validation($$('#paymill_creditcard_cvc')[0].form.id).validate()) {
				this.generateToken();
			}
		}
		
		if (this.helper.getMethodCode() === 'paymill_directdebit') {
			if (new Validation($$('#paymill_directdebit_holdername')[0].form.id).validate()) {
				this.generateToken();
			}
		}
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
    this.setOnClickHandler();
    
};

Paymill.prototype.setOnClickHandler = function(selector)
{
    var that = this;
    
    if ($$(selector)[0]) {
        paymillButton = $$(selector)[0];
        if (!onClickContent) {
            onClickContent = paymillButton.getAttribute('onclick');
            if (paymillButton.getStorage()._object.prototype_event_registry) {
                onClickBounded = paymillButton.getStorage()._object.prototype_event_registry._object.click;
            }   
        }

        $$('input:[name="payment[method]"]').forEach(function(element) {
            element.observe('change', function() {
                paymillButton.removeAttribute('onclick');
                paymillButton.stopObserving('click');
                if (that.helper.getMethodCode() === 'paymill_directdebit') {
                    paymillButton.setAttribute('onclick', 'paymillElv.generateTokenOnSubmit()');
                } else if(that.helper.getMethodCode() === 'paymill_creditcard') {
                    paymillButton.setAttribute('onclick', 'paymillCreditcard.generateTokenOnSubmit()');
                } else {
                    paymillButton.setAttribute('onclick', onClickContent);
                    if (onClickBounded) {
                        onClickBounded.forEach(function (handler) {
                            paymillButton.observe('click', handler);  
                        });
                    }
                }
            });
        });

        if (that.helper.getMethodCode() === 'paymill_directdebit') {
            paymillButton.stopObserving('click');
            paymillButton.removeAttribute('onclick');
            paymillButton.setAttribute('onclick', 'paymillElv.generateTokenOnSubmit()');
        }

        if (that.helper.getMethodCode() === 'paymill_creditcard') {
            paymillButton.stopObserving('click');
            paymillButton.removeAttribute('onclick');
            paymillButton.setAttribute('onclick', 'paymillCreditcard.generateTokenOnSubmit()');
        }
    }
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
            paymillButton.removeAttribute('onclick');
            paymillButton.stopObserving('click');
            paymillButton.setAttribute('onclick', onClickContent);
            if (onClickBounded) {
                onClickBounded.forEach(function (handler) {
                    paymillButton.observe('click', handler);  
                });
            }

            paymillButton.click();
        }
    }
    
    
    
};
