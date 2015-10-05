function PaymillHelper()
{
    
}

PaymillHelper.prototype.getElementValue = function(selector)
{
    var value = '';
    if ($$(selector)[0]) {
        value = $$(selector)[0].value;
    }

    return value;
};

PaymillHelper.prototype.setElementValue = function(selector, value)
{
    if ($$(selector)[0]) {
        $$(selector)[0].value = value;
    }
};

PaymillHelper.prototype.getShortCode = function()
{
    var methods = {
        paymill_creditcard: "cc",
        paymill_directdebit: 'elv'
    };

    if (payment.currentMethod in methods) {
        return methods[payment.currentMethod];
    }

    return 'other';
};

PaymillHelper.prototype.getMethodCode = function()
{
    return payment.currentMethod;
};