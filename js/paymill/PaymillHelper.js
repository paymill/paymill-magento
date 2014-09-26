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

    $$('input:checked[type="radio"][name="payment[method]"]').pluck('value');


    if ($$('input:checked[type="radio"][name="payment[method]"]').pluck('value')) {
        return methods[$$('input:checked[type="radio"][name="payment[method]"]').pluck('value')];
    }

    return 'other';
};

PaymillHelper.prototype.getMethodCode = function()
{
    if ($$('input:checked[type="radio"][name="payment[method]"]').pluck('value')) {
        return $$('input:checked[type="radio"][name="payment[method]"]').pluck('value')[0];
    }

    return 'other';
};