<?php

abstract class Paymill_Paymillcc_Model_PaymentAbstract extends Mage_Payment_Model_Method_Cc
{
    /**
     * Processes the payment against the paymill API
     * @param $params array The settings array
     * @return boolean
     */
    protected function _processPayment($params)
    {

        // setup the logger
        $logger = $params['loggerCallback'];

        // setup client params
        $clientParams = array(
            'email' => $params['email'],
            'description' => $params['name']
        );

        // setup credit card params
        $paymentParams = array(
            'token' => $params['token']
        );

        // setup transaction params
        $transactionParams = array(
            'amount' => $params['amount'],
            'currency' => $params['currency'],
            'description' => $params['description']
        );

        require_once $params['libBase'] . 'Services/Paymill/Transactions.php';
        require_once $params['libBase'] . 'Services/Paymill/Clients.php';
        require_once $params['libBase'] . 'Services/Paymill/Payments.php';

        $clientsObject = new Services_Paymill_Clients(
                        $params['privateKey'], $params['apiUrl']
        );
        $transactionsObject = new Services_Paymill_Transactions(
                        $params['privateKey'], $params['apiUrl']
        );
        $paymentsObject = new Services_Paymill_Payments(
                        $params['privateKey'], $params['apiUrl']
        );

        // perform conection to the Paymill API and trigger the payment
        try {
            if (!array_key_exists('client_id', $params)) {
                $client = $clientsObject->create($clientParams);
                if (!isset($client['id'])) {
                    call_user_func_array($logger, array("No client created" . var_export($client, true)));
                    return false;
                } else {
                    call_user_func_array($logger, array("Client created: " . $client['id']));
                }

                // create card
                $paymentParams['client'] = $client['id'];
            } else {
                $paymentParams['client'] = $params['client_id'];
            }
            
            $payment = $paymentsObject->create($paymentParams);
            if (!array_key_exists('client_id', $params)) {
                if (!isset($payment['id'])) {
                    call_user_func_array($logger, array("No payment (credit card) created: " . var_export($payment, true) . " with params " . var_export($paymentParams, true)));
                    return false;
                } else {
                    call_user_func_array($logger, array("Payment (credit card) created: " . $payment['id']));
                }

                // create transaction
                //$transactionParams['client'] = $client['id'];
                $transactionParams['payment'] = $payment['id'];
            } else {
                $transactionParams['payment'] = $params['payment_id'];
            }
            
            $transaction = $transactionsObject->create($transactionParams);
            
            if(isset($transaction['data']['response_code'])){
                call_user_func_array($logger, array("An Error occured: " . var_export($transaction, true)));
                return false;
            }
            
            if (!isset($transaction['id'])) {
                call_user_func_array($logger, array("No transaction created" . var_export($transaction, true)));
                return false;
            } else {
                $this->_setPaymillClientToken($client['id']);
                $this->_setPaymillPaymentToken($payment['id']);
                $this->_setPaymillTransactionId($transaction['id']);
                call_user_func_array($logger, array("Transaction created: " . $transaction['id']));
            }

            // check result
            if (is_array($transaction) && array_key_exists('status', $transaction)) {
                if ($transaction['status'] == "closed") {
                    // transaction was successfully issued
                    return true;
                } elseif ($transaction['status'] == "open") {
                    // transaction was issued but status is open for any reason
                    call_user_func_array($logger, array("Status is open."));
                    return false;
                } else {
                    // another error occured
                    call_user_func_array($logger, array("Unknown error." . var_export($transaction, true)));
                    return false;
                }
            } else {
                // another error occured
                call_user_func_array($logger, array("Transaction could not be issued."));
                return false;
            }
        } catch (Services_Paymill_Exception $ex) {
            // paymill wrapper threw an exception
            call_user_func_array($logger, array("Exception thrown from paymill wrapper: " . $ex->getMessage()));
            return false;
        }
        
        return true;
    }
    
    protected abstract function _setPaymillClientToken($id);
    
    protected abstract function _setPaymillPaymentToken($id);
    
    protected abstract function _setPaymillTransactionId($id);
}
