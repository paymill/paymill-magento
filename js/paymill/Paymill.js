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
}

Paymill.prototype.validate = function()
{
    return this.methodInstance.validate();
}

Paymill.prototype.createToken = function()
{
    return paymill.createToken(
        this.methodInstance.getTokenParameter(), 
        this.methodInstance.tokenCallback()
    );
}

Paymill.prototype.setValidationRules = function()
{
    this.methodInstance.setValidationRules();
}