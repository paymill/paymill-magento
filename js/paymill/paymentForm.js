//Backend Options
var PAYMILL_PUBLIC_KEY                      = null;

//State Descriptors
var PAYMILL_PAYMENT_NAME                    = "Preparing Payment";
var PAYMILL_IMAGE_PATH                      = null;
var PAYMILL_NO_FAST_CHECKOUT                   = false;

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
    switch(paymill.cardType(pmQuery('.card-number').val())){
        case 'Visa':
            pmQuery('.card-icon').html('<img src="'+ pmQuery('.paymill-info-image-path').val() +'icon_visa.png" >');
            pmQuery('.card-icon').show();
            break;
        case 'MasterCard':
            pmQuery('.card-icon').html('<img src="'+ pmQuery('.paymill-info-image-path').val() +'icon_mastercard.png" >');
            pmQuery('.card-icon').show();
            break;
        default:
            pmQuery('.card-icon').hide();
            debug("Creditcard number not according to any known pattern: "+paymill.cardType(pmQuery('.card-number').val()));
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
        
    switch(PAYMILL_PAYMENT_NAME){
        case "paymill_creditcard":
            PAYMILL_NO_FAST_CHECKOUT = pmQuery('.paymill-info-fastCheckout-cc').val();
            PAYMILL_TOKEN_TOLERANCE = pmQuery('.paymill-option-tokenTolerance-cc').val();
            if(PAYMILL_NO_FAST_CHECKOUT){
                if (false == paymill.validateCardNumber(pmQuery('.card-number').val())) {
                    PAYMILL_ERROR_STRING += PAYMILL_ERROR_TEXT_IVALID_NUMBER_CC;
                    debug(PAYMILL_ERROR_TEXT_IVALID_NUMBER_CC);
                }
                if (false == paymill.validateExpiry(pmQuery('.card-expiry-month').val(), pmQuery('.card-expiry-year').val())) {
                    PAYMILL_ERROR_STRING += PAYMILL_ERROR_TEXT_IVALID_EXPDATE;
                    debug(PAYMILL_ERROR_TEXT_IVALID_EXPDATE);
                }

                if (pmQuery('.card-holdername').val() == '') {
                    PAYMILL_ERROR_STRING += PAYMILL_ERROR_TEXT_IVALID_HOLDER_CC;
                    debug(PAYMILL_ERROR_TEXT_IVALID_HOLDER_CC);
                }

                var params = {
                    amount_int:     (parseInt(pmQuery('.paymill-payment-amount').val()) + parseInt(PAYMILL_TOKEN_TOLERANCE)),  // E.g. "15" for 0.15 Eur
                    currency:       pmQuery('.paymill-payment-currency').val(),    // ISO 4217 e.g. "EUR"
                    number:         pmQuery('.card-number').val(),
                    exp_month:      pmQuery('.card-expiry-month').val(),
                    exp_year:       pmQuery('.card-expiry-year').val(),
                    cvc:            pmQuery('.card-cvc').val(),
                    cardholdername: pmQuery('.card-holdername').val()
                };
            }
            break;

        case "paymill_directdebit":
            PAYMILL_NO_FAST_CHECKOUT = pmQuery('.paymill-info-fastCheckout-elv').val();
            PAYMILL_TOKEN_TOLERANCE = pmQuery('.paymill-option-tokenTolerance-elv').val();
            if(PAYMILL_NO_FAST_CHECKOUT){
                if (false == pmQuery('.elv-holdername').val()) {
                     PAYMILL_ERROR_STRING += PAYMILL_ERROR_TEXT_IVALID_HOLDER_ELV;
                     debug(PAYMILL_ERROR_TEXT_IVALID_HOLDER_ELV);
                }
                 if (false == paymill.validateAccountNumber(pmQuery('.elv-account').val())) {
                     PAYMILL_ERROR_STRING += PAYMILL_ERROR_TEXT_IVALID_NUMBER_ELV;
                     debug(PAYMILL_ERROR_TEXT_IVALID_NUMBER_ELV);
                 }
                 if (false == paymill.validateBankCode(pmQuery('.elv-bankcode').val())) {
                     PAYMILL_ERROR_STRING += PAYMILL_ERROR_TEXT_IVALID_BANKCODE;
                     debug(PAYMILL_ERROR_TEXT_IVALID_BANKCODE);
                 }
                 var params = {
                     amount_int:     pmQuery('.paymill-payment-amount').val() + PAYMILL_TOKEN_TOLERANCE,  // E.g. "15" for 0.15 Eur
                     currency:       pmQuery('.paymill-payment-currency').val(),    // ISO 4217 e.g. "EUR"
                     number:         pmQuery('.elv-account').val(),
                     bank:           pmQuery('.elv-bankcode').val(),
                     accountholder:  pmQuery('.elv-holdername').val()
                };
            }
            break;

        default:
            payment.save();
            return false;
            break;
        }
    if(PAYMILL_ERROR_STRING !== ""){
        debug(PAYMILL_ERROR_STRING);
        // Append Errormessage to form
        pmQuery('.paymill-payment-errors').html(PAYMILL_ERROR_STRING);
        pmQuery('.paymill-payment-errors').show();
        return false;
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
    PAYMILL_ERROR_STRING = "";
    pmQuery('.paymill-payment-errors').hide();
            
    if(PAYMILL_PAYMENT_NAME === "paymill_creditcard"){
        PAYMILL_ERROR_TEXT_IVALID_NUMBER_CC = pmQuery('.paymill-payment-error-number').val();
        PAYMILL_ERROR_TEXT_IVALID_HOLDER_CC = pmQuery('.paymill-payment-error-holder').val();
        PAYMILL_ERROR_TEXT_IVALID_EXPDATE   = pmQuery('.paymill-payment-error-expdate').val();
    }
    
    if(PAYMILL_PAYMENT_NAME === "paymill_directdebit"){
        PAYMILL_ERROR_TEXT_IVALID_NUMBER_ELV = pmQuery('.paymill-payment-error-number').val();
        PAYMILL_ERROR_TEXT_IVALID_HOLDER_ELV = pmQuery('.paymill-payment-error-holder').val();
        PAYMILL_ERROR_TEXT_IVALID_BANKCODE   = pmQuery('.paymill-payment-error-bankcode').val();;
    }
    
    pmQuery('#card-number').live('input', paymillShowCardIcon);
    pmQuery('#payment-buttons-container button:first').prop("onclick", null);
    pmQuery('#payment-buttons-container button:first').unbind('click');
    pmQuery('#payment-buttons-container button:first').click(paymillSubmitForm);
});
