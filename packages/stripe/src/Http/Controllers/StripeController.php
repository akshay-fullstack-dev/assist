<?php

namespace intersoft\stripe\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Validator;

/*
|--------------------------------------------------------------------------
| Register Controller
|--------------------------------------------------------------------------
|
| This controller is used for stripe apis like
| check driver stripe account linked or not, Link stripe account
| create customer, create card
|
*/

class StripeController extends Controller
{
    protected $response = [
        'status' => 0,
        'message' => '',
        'data' => array()
    ];

    public function __construct()
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    }

    /**
     * Check user stripe account linked.
     *
     * @param Request $request
     * @return json  $response
     */

    public function accountLinked()
    {
        try {
            $user = Auth::user();
            $this->response['status'] = ($user->stripe_id) ? 1 : 0;
            $this->response['message'] = ($user->stripe_id) ? "Your account linked to the stripe" : "Account not linked with stripe";
            return response()->json($this->response, 200);
        } catch (Exception $e) {
            $this->response['status'] = 0;
            $this->response['message'] = 'Something went wrong.';
            return response()->json($this->response, 500);
        }
    }

    /**
     * connect stripe account.
     *
     * @param Request $request
     * @return json  $response
     */
    public function linkAccount(Request $request)
    {
        $user = Auth::user();
        $this->response['status'] = 0;
        $validator = Validator::make($request->all(), [
            'token_account' => 'required'
        ]);

        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }

        $token = $request['token_account'];
        try {

            $account = \Stripe\OAuth::token([
                'grant_type' => 'authorization_code',
                'code' => $token,
            ]);
            if ($account) {
                $user->stripe_id = $account->id;
                $user->save();
            }
            // Access the connected account id in the response
            $user->stripe_id = $account->stripe_user_id;
            $user->save();
        } catch (\Exception $e) {
            $this->response['message'] = $e->getMessage();
            return response($this->response, 500);
        }

        $this->response['message'] = 'Account linked successfully';
        $this->response['status'] = 1;
        return response($this->response, 200);
    }

    /**
     * check customer created.
     *
     * @param Request $request
     * @return json  $response
     */
    public function customerExists(Request $request)
    {
        $user = Auth::user();
        if ($user->stripe_id) {
            $this->response['data']['customerId'] = $user->stripe_id;
            $this->response['status'] = 1;
            return response()->json($this->response, 200);
        } else {
            $this->response['data']['customerId'] = '';
            $this->response['status'] = 0;
            return response()->json($this->response, 200);
        }
    }

    /**
     * create customer if not created and create card under that.
     *
     * @param Request $request
     * @return json  $response
     */
    public function createCustomer(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'token_card' => 'required'
        ]);

        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }

        $customer = $user->stripe_id;
        if (!$customer) {
            try {
                $customerObj = \Stripe\Customer::create([
                    'description' => $user->name . '(' . $user->email . ')',
                ]);
                $customer = $customerObj->id;
                $user->stripe_id = $customer;
                $user->save();
            } catch (\Exception $e) {
                $this->response['message'] = $e->getMessage();
                return response($this->response, 500);
            }
        }

        $card_token = $request['token_card'];

        if ($customer) {
            try {
                $card = \Stripe\Customer::createSource(
                    $customer,
                    ['source' => $card_token]
                );
            } catch (\Exception $e) {
                $this->response['message'] = $e->getMessage();
                return response($this->response, 500);
            }
        }
        $this->response['message'] = 'Card created successfully';
        $this->response['data']['customerId'] = ($user->stripe_id) ? $user->stripe_id : "";
        $this->response['status'] = 1;
        return response()->json($this->response, 200);
    }
}
