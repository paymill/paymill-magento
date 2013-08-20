//Backend Options
var PAYMILL_PUBLIC_KEY                      = null;

//State Descriptors
var PAYMILL_PAYMENT_NAME                    = "Preparing Payment";
var PAYMILL_IMAGE_PATH                      = null;
var PAYMILL_NO_FAST_CHECKOUT                = false;

//Errortexts
var PAYMILL_ERROR_STRING                    = "";
var PAYMILL_ERROR_TEXT_IVALID_NUMBER        = null;
var PAYMILL_ERROR_TEXT_IVALID_EXPDATE       = null;
var PAYMILL_ERROR_TEXT_IVALID_HOLDER        = null;
var PAYMILL_ERROR_TEXT_IVALID_BANKCODE      = null;
var PAYMILL_ERROR_TEXT_IVALID_PAYMENT       = null;

/**
 * prints debug messages in the log if debug mode is active
 * @param {String} message
 */
function debug(message)
{   
    debug_state = pmQuery('.paymill-option-debug').val();
    if(debug_state == 1){
        var displayName = "";
        if(PAYMILL_PAYMENT_NAME === 'paymill_creditcard'){
            displayName = 'Credit Card';
        }
        if(PAYMILL_PAYMENT_NAME === 'paymill_directdebit'){
            displayName = 'Direct Debit';
        }
        if(PAYMILL_PAYMENT_NAME === 'Preparing Payment'){
            displayName = 'Preparing Payment';
        }
        
        console.log("["+ displayName +"] " + message);
    }
}

/**
 * Event Handler for the display of the card icons
 */
function paymillShowCardIcon()
{
    switch(paymill.cardType(pmQuery('#paymill_creditcard_number').val())){
        case 'Visa':
            pmQuery('#paymill_creditcard_card_icon').html('<img src="'+ pmQuery('.paymill-info-image-path').val() +'icon_32x20_visa.png" >');
            pmQuery('#paymill_creditcard_card_icon').show();
            break;
        case 'Mastercard':
            pmQuery('#paymill_creditcard_card_icon').html('<img src="'+ pmQuery('.paymill-info-image-path').val() +'icon_32x20_mastercard.png" >');
            pmQuery('#paymill_creditcard_card_icon').show();
            break;
        case 'American Express':
            pmQuery('#paymill_creditcard_card_icon').html('<img src="'+ pmQuery('.paymill-info-image-path').val() +'icon_32x20_amex.png" >');
            pmQuery('#paymill_creditcard_card_icon').show();
            break;
        case 'JCB':
            pmQuery('#paymill_creditcard_card_icon').html('<img src="'+ pmQuery('.paymill-info-image-path').val() +'icon_32x20_jcb.png" >');
            pmQuery('#paymill_creditcard_card_icon').show();
            break;
        case 'Maestro':
            pmQuery('#paymill_creditcard_card_icon').html('<img src="'+ pmQuery('.paymill-info-image-path').val() +'icon_32x20_maestro.png" >');
            pmQuery('#paymill_creditcard_card_icon').show();
            break;
        case 'Diners Club':
            pmQuery('#paymill_creditcard_card_icon').html('<img src="'+ pmQuery('.paymill-info-image-path').val() +'icon_32x20_dinersclub.png" >');
            pmQuery('#paymill_creditcard_card_icon').show();
            break;
        case 'Discover':
            pmQuery('#paymill_creditcard_card_icon').html('<img src="'+ pmQuery('.paymill-info-image-path').val() +'icon_32x20_discover.png" >');
            pmQuery('#paymill_creditcard_card_icon').show();
            break;
        default:
            pmQuery('#paymill_creditcard_card_icon').hide();
            debug("Creditcard number not according to any known pattern: "+paymill.cardType(pmQuery('#paymill_creditcard_number').val()));
            break;
    }
}

/**
 * Handles the response of the paymill bridge. saves the token in a formfield.
 * @param {Boolean} error
 * @param {response object} result
 */
function paymillResponseHandler(error, result) 
{
    if (error) {
        // Appending error
        PAYMILL_ERROR_STRING += error.apierror + "\n";
        debug(error.apierror);
        debug("Paymill Response Handler triggered: Error.");
    } else {
        // Appending Token to form
        debug("Saving Token in Form: "+result.token);
        pmQuery('.paymill-payment-token').val(result.token);
        payment.save();
    }
}

/**
 * 
 * @returns {Boolean}
 */
function paymillSubmitForm()
{
    PAYMILL_PAYMENT_NAME = pmQuery("input[name='payment[method]']:checked").val();
    
    if(PAYMILL_PAYMENT_NAME === "paymill_creditcard"){
        paymill.config('3ds_cancel_label', pmQuery('.paymill_3ds_cancel').val()); 
        PAYMILL_ERROR_TEXT_IVALID_NUMBER_CC = pmQuery('.paymill-payment-error-number').val();
        PAYMILL_ERROR_TEXT_IVALID_HOLDER_CC = pmQuery('.paymill-payment-error-holder').val();
        PAYMILL_ERROR_TEXT_IVALID_CVC       = pmQuery('.paymill-payment-error-cvc').val();
        PAYMILL_ERROR_TEXT_IVALID_EXPDATE   = pmQuery('.paymill-payment-error-expdate').val();
    }
    
    if(PAYMILL_PAYMENT_NAME === "paymill_directdebit"){
        PAYMILL_ERROR_TEXT_IVALID_NUMBER_ELV = pmQuery('.paymill-payment-error-number-elv').val();
        PAYMILL_ERROR_TEXT_IVALID_HOLDER_ELV = pmQuery('.paymill-payment-error-holder-elv').val();
        PAYMILL_ERROR_TEXT_IVALID_BANKCODE   = pmQuery('.paymill-payment-error-bankcode').val();;
    }
    
    switch(PAYMILL_PAYMENT_NAME){
        case "paymill_creditcard":
            PAYMILL_NO_FAST_CHECKOUT = pmQuery('.paymill-info-fastCheckout-cc').val();
            if(PAYMILL_NO_FAST_CHECKOUT){
                var paymillValidator = new Validation('co-payment-form');
                var nv = {};
                nv['paymill-validate-cc-number'] = new Validator('paymill-validate-cc-number', PAYMILL_ERROR_TEXT_IVALID_NUMBER_CC, function(v) {
                    if (false == paymill.validateCardNumber(pmQuery('#paymill_creditcard_number').val())) {
                        debug(PAYMILL_ERROR_TEXT_IVALID_NUMBER_CC);
                        return false;
                    }
                    return true;
                }, '');
                nv['paymill-validate-cc-expdate'] = new Validator('paymill-validate-cc-expdate', PAYMILL_ERROR_TEXT_IVALID_EXPDATE, function(v) {
                    if (false == paymill.validateExpiry(pmQuery('#paymill_creditcard_expiry_month').val(), pmQuery('#paymill_creditcard_expiry_year').val())) {
                        debug(PAYMILL_ERROR_TEXT_IVALID_EXPDATE);
                        return false;
                    }
                    return true;
                }, '');
                nv['paymill-validate-cc-holder'] = new Validator('paymill-validate-cc-holder', PAYMILL_ERROR_TEXT_IVALID_HOLDER_CC, function(v) {
                    if (pmQuery('#paymill_creditcard_holdername').val() == '') {
                        debug(PAYMILL_ERROR_TEXT_IVALID_HOLDER_CC);
                        return false;
                    }
                    return true;
                }, '');
                nv['paymill-validate-cc-cvc'] = new Validator('paymill-validate-cc-cvc', PAYMILL_ERROR_TEXT_IVALID_CVC, function(v) {
                    if (false == paymill.validateCvc(pmQuery('#paymill_creditcard_cvc').val())) {
                        debug(PAYMILL_ERROR_TEXT_IVALID_CVC);
                        return false;
                    }
                    return true;
                }, '');

                Object.extend(Validation.methods, nv);

                if(!paymillValidator.validate()) {
                    return false;
                }
                
                var params = {
                    amount_int:     parseInt(pmQuery('.paymill-payment-amount').val()),  // E.g. "15" for 0.15 Eur
                    currency:       pmQuery('.paymill-payment-currency').val(),    // ISO 4217 e.g. "EUR"
                    number:         pmQuery('#paymill_creditcard_number').val(),
                    exp_month:      pmQuery('#paymill_creditcard_expiry_month').val(),
                    exp_year:       pmQuery('#paymill_creditcard_expiry_year').val(),
                    cvc:            pmQuery('#paymill_creditcard_cvc').val(),
                    cardholdername: pmQuery('#paymill_creditcard_holdername').val()
                };
            }
            break;

        case "paymill_directdebit":
            PAYMILL_NO_FAST_CHECKOUT = pmQuery('.paymill-info-fastCheckout-elv').val();
            if(PAYMILL_NO_FAST_CHECKOUT){
                var paymillValidator = new Validation('co-payment-form');
                var nv = {};
                nv['paymill-validate-dd-holdername'] = new Validator('paymill-validate-dd-holdername', PAYMILL_ERROR_TEXT_IVALID_HOLDER_ELV, function(v) {
                    if (false == pmQuery('#paymill_directdebit_holdername').val()) {
                        debug(PAYMILL_ERROR_TEXT_IVALID_HOLDER_ELV);
                        return false;
                    }
                    return true;
                }, '');
                nv['paymill-validate-dd-account'] = new Validator('paymill-validate-dd-account', PAYMILL_ERROR_TEXT_IVALID_NUMBER_ELV, function(v) {
                    if (false == paymill.validateAccountNumber(pmQuery('#paymill_directdebit_account').val())) {
                        debug(PAYMILL_ERROR_TEXT_IVALID_NUMBER_ELV);
                        return false;
                    }
                    return true;
                }, '');
                nv['paymill-validate-dd-bankcode'] = new Validator('paymill-validate-dd-bankcode', PAYMILL_ERROR_TEXT_IVALID_BANKCODE, function(v) {
                    if (false == paymill.validateBankCode(pmQuery('#paymill_directdebit_bankcode').val())) {
                        debug(PAYMILL_ERROR_TEXT_IVALID_BANKCODE);
                        return false;
                    }
                    return true;
                }, '');

                Object.extend(Validation.methods, nv);
                
                if(!paymillValidator.validate()) {
                    return false;
                }
  
                var params = {
                    amount_int:     pmQuery('.paymill-payment-amount').val(),  // E.g. "15" for 0.15 Eur
                    currency:       pmQuery('.paymill-payment-currency').val(),    // ISO 4217 e.g. "EUR"
                    number:         pmQuery('#paymill_directdebit_account').val(),
                    bank:           pmQuery('#paymill_directdebit_bankcode').val(),
                    accountholder:  pmQuery('#paymill_directdebit_holdername').val()
                };
            }
            break;

        default:
            payment.save();
            return false;
            break;
    }

    if(PAYMILL_NO_FAST_CHECKOUT){
        debug("Generating Token");
        paymill.createToken(params, paymillResponseHandler);
    } else {
        debug("FastCheckout Data found. Skipping Token generation.");
        payment.save();
    }
        
    return false;
}


pmQuery(document).ready(function() 
{
    //Gather Data
    PAYMILL_PUBLIC_KEY   = pmQuery('.paymill-info-public_key').val();
    pmQuery('#paymill_creditcard_number').live('input', paymillShowCardIcon);
    pmQuery('#payment-buttons-container button:first').prop("onclick", null);
    pmQuery('#payment-buttons-container button:first').unbind('click');
    pmQuery('#payment-buttons-container button:first').click(paymillSubmitForm);
});
