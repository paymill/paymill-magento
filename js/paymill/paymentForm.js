//Backend Options
var PAYMILL_PUBLIC_KEY                      = null;
var PAYMILL_DEBUG_STATE                     = null;
var PAYMILL_SHOW_LOGO                       = null;

//State Descriptors
var PAYMILL_PAYMENT_NAME                    = null;
var PAYMILL_IMAGE_PATH                      = null;

//Errortexts
var PAYMILL_ERROR_STRING                    = "";
var PAYMILL_ERROR_TEXT_IVALID_NUMBER        = null;
var PAYMILL_ERROR_TEXT_IVALID_EXPDATE       = null;
var PAYMILL_ERROR_TEXT_IVALID_HOLDER        = null;
var PAYMILL_ERROR_TEXT_IVALID_BANKCODE      = null;
var PAYMILL_ERROR_TEXT_IVALID_PAYMENT       = null;

function debug(message)
{
    if(PAYMILL_DEBUG_STATE){
        var displayName = PAYMILL_PAYMENT_NAME === 'paymill_credit_card' ? 'Credit Card' : 'Direct Debit';
        console.log("["+ displayName +"] " + message);
    }
}

function setPublicKey(key)
{
    PAYMILL_PUBLIC_KEY = key;
}

function setDebugState(state)
{
    PAYMILL_DEBUG_STATE = state;
}

function setShowLogo(showLogo)
{
    PAYMILL_SHOW_LOGO = showLogo;
}

function setPaymentName(name)
{
    PAYMILL_PAYMENT_NAME = name;
}

function setImagePath(path)
{
    PAYMILL_IMAGE_PATH = path + "/icon_";
}

function setErrorTextInvalidNumber(text)
{
    PAYMILL_ERROR_TEXT_IVALID_NUMBER = text;
}

function setErrorTextInvalidExpDate(text)
{
    PAYMILL_ERROR_TEXT_IVALID_EXPDATE = text;
}

function setErrorTextInvalidHolder(text)
{
    PAYMILL_ERROR_TEXT_IVALID_HOLDER = text;
}

function setErrorTextInvalidBankcode(text)
{
    PAYMILL_ERROR_TEXT_IVALID_BANKCODE = text;
}

function setErrorTextInvalidPayment(text)
{
    PAYMILL_ERROR_TEXT_IVALID_PAYMENT = text;
}

pmQuery = jQuery.noConflict();
pmQuery(document).ready(function() {
    pmQuery('#payment-buttons-container button:first').prop("onclick", null);
    pmQuery('#payment-buttons-container button:first').unbind('click');
    pmQuery('#payment-buttons-container button:first').click(PaymillSubmitForm);
    
    //Gather Data
    setPaymentName(pmQuery('.paymill-payment-name').val());
    setPublicKey(pmQuery('.paymill-info-public_key').val());
    setDebugState(pmQuery('.paymill-option-debug').val());
    setShowLogo(pmQuery('.paymill-option-logo').val());
    setImagePath(pmQuery('.paymill-info-image-path').val());
    setErrorTextInvalidNumber(pmQuery('.paymill-payment-error-number').val());
    setErrorTextInvalidHolder(pmQuery('.paymill-payment-error-holder').val());
    setErrorTextInvalidPayment(pmQuery('.paymill-payment-error-payment').val());
        
    if(PAYMILL_PAYMENT_NAME === "paymill_credit_card"){
        setErrorTextInvalidExpDate(pmQuery('.paymill-payment-error-expdate').val());
        pmQuery('.card-number').keypress(PaymillShowCardIcon());
    }
    
    if(PAYMILL_PAYMENT_NAME === "paymill_direct_debit"){
        setErrorTextInvalidBankcode(pmQuery('.paymill-payment-error-bankcode').val());
    }
    
      
    function PaymillShowCardIcon()
    {
        switch(paymill.cardType(pmQuery('.card-number').val())){
            case 'Visa':
                pmQuery('.card-icon').html('<img src='+ PAYMILL_IMAGE_PATH +'"visa.png" >');
                pmQuery('.card-icon').show();
                break;
            case 'MasterCard':
                pmQuery('.card-icon').html('<img src='+ PAYMILL_IMAGE_PATH +'"mastercard.png" >');
                pmQuery('.card-icon').show();
                break;
            default:
                pmQuery('.card-icon').hide();
                break;
        }
    }

    function PaymillResponseHandler(error, result) {
        if (error) {
            // Zeigt den Fehler überhalb des Formulars an
            PAYMILL_ERROR_STRING += error.apierror;
            debug(error.apierror);
        } else {
            // Token
            var token = result.token;
            // Token in das Formular einfügen damit es an den Server übergeben wird
            pmQuery('.paymill-token').val(token);
        }
    }
    
    function PaymillSubmitForm(){
        switch(PAYMILL_PAYMENT_NAME){
            case "paymill_credit_card":
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
                    amount_int:     pmQuery('.paymill-payment-amount').val(),  // E.g. "15" for 0.15 Eur
                    currency:       pmQuery('.paymill-payment-currency').val(),    // ISO 4217 e.g. "EUR"
                    number:         pmQuery('.card-number').val(),
                    exp_month:      pmQuery('.card-expiry-month').val(),
                    exp_year:       pmQuery('.card-expiry-year').val(),
                    cvc:            pmQuery('.card-cvc').val(),
                    cardholdername: pmQuery('.card-holdername').val()
                };
                break;
                
            case "paymill_direct_debit":
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
                    amount_int:     pmQuery('.paymill-payment-amount').val(),  // E.g. "15" for 0.15 Eur
                    currency:       pmQuery('.paymill-payment-currency').val(),    // ISO 4217 e.g. "EUR"
                    number:         pmQuery('.elv-account').val(),
                    bank:           pmQuery('.elv-bankcode').val(),
                    accountholder:  pmQuery('.elv-holdername').val()
                };
                break;
                
            default:
                payment.save();
                break;
        }
        if(PAYMILL_ERROR_STRING !== ""){
            pmQuery('.paymill-payment-error-string').val(PAYMILL_ERROR_STRING);
            return false;
        }
        Debug("Generating Token");
        paymill.createToken(params, PaymillResponseHandler);
        
        payment.save();
        return false;
    }
});