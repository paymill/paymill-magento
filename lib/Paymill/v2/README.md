# Paymill-PHP

## Getting started with Paymill

1.  Include the required PHP file from the paymill PHP library. For example via: 

        require_once 'lib/Services/Paymill/Transactions.php';

2.  Instantiate the class, for example the Services_Paymill_Transactions class, with the following parameters:

    $apiKey: First parameter is always your private API (test) Key

    $apiEndpoint: Second parameter is to configure the API Endpoint (with ending /), e.g. "https://api.paymill.de/v2/"
	
        $transactionsObject = new Services_Paymill_Transactions($apiKey, $apiEndpoint);

3.  Make the appropriate call on the class instance. For additional parameters please refer to the API-Reference:

        $transactionsObject->create($params);


##PaymentProcessor: An easy way to use Paymill for your payment processes
This PaymentProcessor class is located in the Services directory of the library and is meant to ease your implementation of payment processes. To use this new feature, simply follow this steps:

1.  Include the required PHP file from the paymill PHP library. For example via:
		require_once 'lib/Services/PaymentProcessor.php';
        
2.  Create a new Object from the class using either:
	
    2.a. The basic constructor (you will be required to set up additional information using setters)
    
    	$processor = new PaymentProcessor($privateKey, $apiUrl);
           
    2.b. The full constructor (you can pass an array of values as the constructors argument, freeing you from the use of
    setters)
    
    	$processor = new PaymentProcessor($privateKey, $apiUrl, $libBase = null, $params, $loggingClassInstance);
        
   $libBase is an optional parameter storing the path to the libBase directory as a String. This is only required if you plan on storing the paymentprocessor.php file in another location than the current.
        
   $params is an array of all properties the class needs to work as intended.
   Those Parameters are:
    *    <b>token</b>,               generated Token
    *    <b>authorizedAmount</b>,    Tokenamount
    *    <b>amount</b>,              Basketamount 
    *    <b>currency</b>,            Transaction currency
    *    <b>payment</b>,             The chosen payment (cc | elv)
    *    <b>name</b>,                Customer name
    *    <b>email</b>,               Customer emailaddress
    *    <b>description</b>,         Description for transactions
   
   Each of the $params entries musst be formatted like this example: <b>$params['token'] => 'mySampleToken'</b>
   
   $loggingClassInstance can be any class implementing a log(String,String) method. This parameter is optional. If you set it to null, no logging will be done within the payment process.
   
3. If you chose 2.b skip this step. Otherwise you want to add additional information using the following setters now:

		$processor->setToken(String);
        $processor->setAuthorizedAmount(String);
        $processor->setAmount(String);
        $processor->setCurrency(String);
        $processor->setPayment(String);
        $processor->setName(String);
        $processor->setEmail(String);
        $processor->setDescription(String);
        
4. If you chose not to provide a loggingClassInstance in the controller call, you can still do this now using the setLogger() Method.

5. To process the chosen payment you can now simply call:
		
        $result = $processor->processPayment();
        
    To determine if the payment was successful, processPayment() will return a boolean.


## API versions

The master branch reflects the newest API version, which is v2 for now. In order to use an older version just checkout the corresponding tag.
	
For further information, please refer to our official PHP library reference: https://www.paymill.com/en-gb/documentation-3/reference/api-reference/index.html
