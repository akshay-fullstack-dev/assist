<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Braintree_ClientToken;
use Braintree\Transaction;

class BraintreeTokenController extends Controller
{
    public function index()
    {  
        try { 
            $this->response['message'] = 'Generate token succesfully';
            $this->response['status'] = 1;
            $this->response['data']['token'] = Braintree_ClientToken::generate();
            
            
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['status'] = 0;
            $this->response['data'] = (object) [];
            $this->response['message'] = "Something went wrong";
            return response($this->response, 500);
        }
    }

    public function payment(Request $request)
    {
        
         
        
        $result = Transaction::sale([
            'amount' => $request['amount'],
            'paymentMethodNonce' => $request['payment_method_nounce'],
            'orderId' => 11,
        ]);
         
        if ($result->success) {
            print_r("Success ID: " . $result->transaction->id);
        } else {
            print_r("Error Message: " . $result->message);
        }
    }

}
