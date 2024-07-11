<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserPayment;
use App\UserPackage;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\User;

class PaymentController extends Controller
{
    public $response = [
        'status' => 0,
        'message' => '',
        'data' => array()
    ];

    public function PurchaseSubscription(Request $request)
    {
        try {

            // if request from android plateform
            $validator = Validator::make($request->all(), [
                // 1 for and roid and 0 for ios request
                'platform' => ['required', Rule::in([1, 0])],
                'package_name' => ['required'],
                'purchase_token' => ['required'],
                'product_id' => ['required'],
                'transaction_date' => ['required'],
                'order_id' => ['required']
            ]);
            if ($validator->fails()) {
                $this->response['status'] = false;
                $this->response['message'] = $validator->errors()->first();
                return response()->json($this->response, 401);
            }
            $user = Auth::user();

            // find if user already purchased this package
            $user_package = UserPackage::where(['user_id' => $user->id, 'purchase_token' => (string) $request->purchase_token])->get();

            if ($user_package->count()) {
                $this->response['status'] = 0;
                $this->response['message'] = trans('api/user.already_purchased');
                return response($this->response, 400);
            }

            // if there is new package for a perticaulr user then inster new package
            $package = UserPackage::create([
                'user_id' => $user->id,
                'platform' => $request->platform,
                'package_name' => $request->package_name,
                'purchase_token' => $request->purchase_token,
                'product_id' => $request->product_id,
                'order_id' => $request->order_id,
                'developer_payload' => $request->developer_payload ? $request->developer_payload : "",
                'transaction_date' => date('Y-m-d', (int) $request->transaction_date)
            ]);
            if ($package) {
                $user->payment_status = '1';
                $user->save();
                $this->response['status'] = 1;
                $this->response['message'] = trans('api/user.package_purchased_success');
                return response($this->response, 201);
            } else {
                $this->response['status'] = 1;
                $this->response['message'] = trans('api/user.record_not_inserted_due_to_server_error');
                return response($this->response, 409);
            }
        } //</try>

        //catch exception
        catch (Exception $e) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 409);
        }
    }


    // get the subcrition of the user
    /**
     * function name :- GetSubscription
     * @param none
     * @return resposne
     */
    public function GetSubscription(Request $request)
    {
        $user = Auth::user();
        $package = UserPackage::where('user_id', $user->id)->latest('created_at')->first();

        // if package not found return message package not found and return from here
        if (!$package) {
            $this->response['message'] = trans('api/user.payment_not_found');
            return response($this->response, 200);
        }
        $build_mode = '';
        if (isset($request->build) and $request->build == UserPackage::testEnvironment) {
            $build_mode = UserPackage::testEnvironment;
        } else {
            $build_mode = UserPackage::liveEnvironment;
        }
        // if package purchase was ios then get the package details from ios server
        if ($package->platform == UserPackage::iosPlatform) {

            $response = $this->get_ios_package_from_apple_server($package, $user, $build_mode);
        }
        // process for the android
        else {
            $response = $this->get_android_package_details($package, $user);
        }
        return response($response, 200);
    }


    // get the package from the ios server
    // called by :-GetSubscription
    private function get_ios_package_from_apple_server($package_data, $user, $build_mode)
    {
        $apple_response = UserPackage::getIosPackageDetails($package_data, $build_mode);
        if (!$apple_response) {
            $package_data->status = UserPackage::activePackage;
            $package_data->users->payment_status = UserPackage::inActivePackage;
            $package_data->save();
            $this->response['message'] = trans('api/user.package_cannot_verify_on_ios');
            return $this->response;
        }
        // if package expire 
        if ((($apple_response['latest_receipt_info'][0]['expires_date_ms']) / 1000) <  strtotime(Carbon::now())) {
            $package_data->status = UserPackage::activePackage;
            $package_data->users->payment_status = UserPackage::inActivePackage;
            $package_data->save();
            $this->response['data'] = 'hase todend yet'; //!change this response
            $this->response['message'] = trans('api/user.package_expired');
        } else {
            // if every thing ok then we can make user payment active
            $package_data->status = UserPackage::activePackage;
            $package_data->users->payment_status = UserPackage::activePackage;
            $package_data->save();
            $this->response['status'] = 1;
            $this->response['data'] = 'has to change'; //!change this response
            $this->response['message'] = trans('api/user.payment_found');
            return $this->response;
        }
    }

    private function get_android_package_details($package, $user)
    {
        // if google token not found
        if (!$access_token = UserPackage::GetGoogleToken()) {
            $this->response['message'] = trans('api/user.google_token_not_found');
            return $this->response;
        }
        // get user package
        $google_response =  UserPackage::GetUserPackageDetailsFromGoogle($package->purchase_token, $access_token, $package->product_id);
        if ($google_response != false) {
            // save package expiry date in package table
            $package->status = UserPackage::activePackage;
            $package->expiry_date =  date("Y-m-d H:i:s", substr($google_response['expiryTimeMillis'], 0, 10));
            $package->save();

            // compare google code expirty date  of package with current date 
            if (($google_response['expiryTimeMillis'] / 1000) <  strtotime(Carbon::now())) {
                $user->payment_status = UserPackage::inActivePackage;
                $user->save();
                $this->response['data'] = new UserPayment($google_response);
                $this->response['message'] = trans('api/user.package_expired');
                return $this->response;
            } else {
                $user->payment_status = UserPackage::activePackage;
                $user->save();
                $this->response['status'] = 1;
                $this->response['data'] = new UserPayment($google_response);
                $this->response['message'] = trans('api/user.payment_found');
                return $this->response;
            }
        } else {
            // if package not found the send user response that package not found change user payment user
            $user->payment_status = UserPackage::inActivePackage;
            $user->save();
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/user.package_not_found_on_google');
            return $this->response;
        }
    }
}
