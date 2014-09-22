function PaymillHelper()
{
    
}

PaymillHelper.prototype.getElementValue = function(selector)
{
    var value = '';
    if ($(selector)[0]) {
        value = $(selector)[0].value;
    }

    return value;
}