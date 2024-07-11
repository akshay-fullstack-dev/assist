<?php

namespace App\Http\Controllers\API;

use Symfony\Component\HttpFoundation\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\User;
use App\UserAddresses;
use App\VenderService;
use App\Mail\Activate;
use App\Mail\ForgetPassword;
use App\Mail\UpdateEmail;
use Illuminate\Support\Facades\Auth;
use App\User_activation as Activation;
use Lcobucci\JWT\Parser;
use App\DeviceDetails;
use App\Models\Role;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Avatar as AvatarResource;
use App\Http\Resources\RolesResourceCollection;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Support\Facades\Input;
use App\Rules\PhoneNumber;
use App\Services\PushNotification;
use App\Services\ScheduleService;
use App\phoneOtp;
use Aloha;
use Illuminate\Support\Facades\DB;
use App\slot;
use App\Notification;
use App\AvatarImage;
use App\Service;
use App\userCoupon;
use App\Helpers\Common;
use App\Traits\SandgridTrait;
use Exception;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{

    use \App\Traits\ApiUserTrait;
    use \App\Traits\PaybaseApiTrait;
    use SandgridTrait;

    protected $response = [
        'status' => 0,
        'message' => '',
    ];

    public function __construct()
    {
        $this->response['data'] = new \stdClass();
    }

    // verify vender phone
    public function checkPhoneOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => ['required'],
            'id' => ['required']
        ]);

        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }

        $user = User::where(['id' => $request['id']])->first();
        if (!$user) {
            $this->response['message'] = trans('api/user.user_not_exist');
            return response()->json($this->response, 404);
        }

        if ($user->is_verified == User::active) {
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/user.user_already_verified');
            return response()->json($this->response, 200);
        }

        $user = User::where(['otp' => $request['otp'], 'id' => $request['id']])->first();

        if (!$user) {
            $this->response['message'] = trans('api/user.otp_phone_not_match');
            return response()->json($this->response, 404);
        }
        $user->is_verified = User::verified;
        $user->save();
        // phoneOtp::where('phone_no', '=', $oldPhone)->delete();
        // $user->phone_number = $confirmOtp->phone_no;
        // phoneOtp::where('phone_no', '=', $oldPhone)->delete();
        // $user->phone_number = $confirmOtp->phone_no;
        // $user->status = '0';
        // $filename = 'api_datalogger_' . date('d-m-y') . '.log';
        // $user->save();
        $token = $user->createToken('Api access token')->accessToken;
        $this->insertDeviceDetails($token, $request['id']);

        return (new UserResource($user, $token))->additional([
            'status' => 1,
            'message' => trans('api/user.otp_matched')
        ]);
    }

    private function generate_random_string()
    {
        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
            . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . '0123456789'); // and any other characters
        shuffle($seed); // probably optional since array_is randomized; this may be redundant
        $rand = '';
        foreach (array_rand($seed, 5) as $k)
            $rand .= $seed[$k];

        return $rand;
    }

    /**
     * @social login
     * @param Request $request
     * @return type
     */
    public function socialLogin(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'fb_id' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }

        if ($request->fb_id) {
            $user = User::where('fb_id', $request->fb_id)->first();
            if ($user) {
                $token = $user->createToken('Api access token')->accessToken;
                $this->insertDeviceDetails($token, $user->id);
                return (new UserResource($user, $token))->additional([
                    'currentStatus' => 1,
                    'status' => 1,
                    'message' => trans('api/user.loggedin_successfully')
                ]);
            }
            $user = User::create([
                'fb_id' => $request->fb_id,
                'user_type' => "1", //it defines user in the login with facebook 
                'email' => $request->email ? $request->email : '',
                'phone_number' => $request->phone_number ? $request->phone_number : '',
            ]);
            $user->attachRole(1); // its a user
            $token = $user->createToken('Api access token')->accessToken;
            $this->insertDeviceDetails($token, $user->id);
            return (new UserResource($user, $token))->additional([
                'currentStatus' => 1,
                'status' => 1,
                'message' => trans('api/user.loggedin_successfully')
            ]);
        }
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string|max:45',
            // 'last_name' => 'required_if:role_id,==,2',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
            'phone' => 'required|min:8',
            'vender_doc' => 'required_if:role_id,==,2',
            'role_id' => 'required',
            'gender' => 'required|in:0,1',
            // 'otp' => 'required_if:role_id,==,1',
            'address.place_id' => 'required',
            'address.longitude' => 'required',
            'address.latitude' => 'required',
            'address.postal_code' => 'required'
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        try {

            $user_bio = isset($request->bio) ? $request->bio : " ";

            if (isset($request['refferal_code']) && $request['refferal_code'] != '') {
                $check_referal = User::where('refferal', $request['refferal_code'])->first();
                if (!$check_referal) {
                    $this->response['message'] = trans('api/user.referal_not_exist');
                    return response()->json($this->response, 409);
                }
            }
            $email = strtolower($request['email']);

            if (!$request->fb_id) {
                $check_eamil_already_taken = User::where('email', $email)->first();
                if ($check_eamil_already_taken) {
                    $this->response['message'] = trans('api/user.email_already_exist');
                    return response()->json($this->response, 409);
                }

                $check_phone_already_taken = User::where('phone_number', $request['phone'])->first();
                if ($check_phone_already_taken) {
                    $this->response['message'] = trans('api/user.phone_already_registered');
                    return response()->json($this->response, 409);
                }
            }

            // if venodor login with wrong service id then should not be register

            if ($request->role_id == 2) {
                if (isset($request->services)) {
                    foreach ($request->services as $service)
                        $service = Service::where('cat_id', $service['sevice_id'])->first();

                    if (empty($service)) {
                        $this->response['message'] = trans('api/service.service_not_found');
                        return response()->json($this->response, 404);
                    }
                }
            }


            // if ($request['role_id'] != 2) {
            //     $confirmOtp = phoneOtp::where(array('otp' => $request['otp'], 'phone_no' => $request['phone']))->first();
            // ! remove comments when it goes live
            //     if (!$confirmOtp) {
            //         $this->response['message'] = trans('api/user.otp_phone_not_match');
            //         return response()->json($this->response, 404);
            //     }
            // } else {
            //     $otp = rand(10000, 99999);
            //     $phone = $request['phone'];
            //     $twilio = new Aloha\Twilio\Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_SMS_FROM_NUMBER'));
            //     if ($twilio->message($phone, 'Otp is ' . $otp)) {
            //         $user_otp = phoneOtp::where('phone_no', $request['phone'])->first();
            //         if ($user_otp) {
            //             $user_otp->update(['otp' => $otp]);
            //         } else {
            //             $phoneOtp = phoneOtp::create(['phone_no' => $request['phone'], 'otp' => $otp]);
            //         }
            //     }
            // $otp = 4444;
            // $user_otp = phoneOtp::where('phone_no', $request['phone'])->first();
            // if ($user_otp) {
            //     $user_otp->update(['otp' => $otp]);
            // } else {
            //     $phoneOtp = phoneOtp::create(['phone_no' => $request['phone'], 'otp' => $otp]);
            // }
            // }
            $user_check = User::where('email', $email)->first();
            $role = Role::find($request['role_id']);
            if (!$role) {
                $this->response['message'] = trans('api/user.role_not_exist');
                return response()->json($this->response, 404);
            }
            // $status = User::not_verified;

            if ($request['role_id'] == 2) {
                $status = User::pending;
            } else {
                $status = User::active;
            }
            // ! need to remove when it goes live
            // $phoneotp = new phoneOtp;
            // $phoneotp->phone_no = $request['phone'];
            // $phoneotp->otp = 4444;
            // $phoneotp->save();
            $otp = '4444';
            // ! THIS WHOLE OTP SECTION WILl BE DELETED

            //create vendor/user as a customer in paybase
            // $paybaseResdentialAddress = array(
            //     'postalCode' => $request['address']['postal_code'],
            //     'countryISO' => $request['address']['country_iso'],
            //     "houseNameNumber" => $request['address']['house_number'],
            //     "street" => $request['address']['street'],
            //     "townCity" => $request['address']['town_city']
            // );

            // $body['roleSlug'] = ($request['role_id'] == 1) ? "customer" : "worker";
            // $body['profile']['firstName'] = $request['first_name'];
            // $body['profile']['lastName'] = $request['last_name'];
            // $body['profile']['email'] = $request['email'];
            // $body['profile']['dob'] = str_replace('+00:00', 'Z', gmdate('c', strtotime($request['dob'])));
            // $body['profile']['phoneNumber'] = $request['phone'];
            // $body['profile']['residentialAddress'] = $paybaseResdentialAddress;
            // $body['terms'] = array("acceptedAt" => str_replace('+00:00', 'Z', gmdate('c')), "revision" => "2.1");

            //create paybase customer
            // try {
            //     if ($request['role_id'] == 1) {
            //         $paybaseUser = $this->paybaseApi($body, "https://api-json.sandbox.paybase.io/v1/customers/individual");
            //     } else {
            //         $paybaseUser = $this->paybaseApi($body, "https://api-json.sandbox.paybase.io/v1/customers/sole-trader");
            //     }

            //     $paybaseUserId = $paybaseUser->id;
            // } catch (\Exception $ex) {
            //     $this->response['message'] = $this->paybaseExceptionErrorMessage($ex);
            //     if ($this->response['message'] = "")
            //         $this->response['message'] = trans('api/user.paybae_error');
            //     return response($this->response, $ex->getCode());
            // }

            if (isset($request->fb_id) && $request->fb_id != '') {
                $user = User::where('fb_id', $request->fb_id)->first();
                if ($user) {
                    $user->firstname = $request->user_name;
                    $user->email = $email;
                    $user->phone_number = $request->phone;
                    $user->image = $request->image;
                    $user->gender = $request->gender;
                    $user->status = $status;
                    $user->bio = $user_bio;
                    $user->is_verified = User::not_verified;
                    $user->otp = $otp;
                    $user->save();
                } else {
                    $this->response['message'] = trans('api/user.user_not_exist');
                    return response($this->response, 404);
                }
            } else {
                $user = User::create([
                    'firstname' => $request['user_name'],
                    'email' => $email,
                    'password' => Hash::make($request['password']),
                    'phone_number' => $request['phone'],
                    'reffer_code' => $request['refferal_code'],
                    'bio' => $user_bio,
                    // !this otp field is statis otp this has to be removed when it goes live
                    'otp' => $otp,
                    'image' => $request['profile_image'] ? $request['profile_image'] : '',
                    'vender_doc' => $request['vender_doc'] ? $request['vender_doc'] : '',
                    'refferal' => $this->generate_random_string(),
                    'gender' => isset($request['gender']) ? $request['gender'] : '0',
                    'status' => $status,
                    'is_verified' => User::not_verified
                ]);
            }
            if ($request['role_id'] == 2) {

                if ($request['services']) {
                    foreach ($request['services'] as $servic) {
                        if ($servic) {
                            foreach ($servic['sub_services'] as $sub_service) {
                                VenderService::create([
                                    'vender_id' => $user->id,
                                    'service_id' => $sub_service,
                                    'cat_id' => $servic['sevice_id'],
                                ]);
                            }
                        }
                    }
                }
            }
            // if ($request['role_id'] != 2) {
            //     phoneOtp::where('id', $confirmOtp->id)->update(['is_verified' => 1]);
            // }

            $user_adress = UserAddresses::create([
                'user_id' => $user->id,
                'place_id' => $request['address']['place_id'],
                'latitude' => $request['address']['latitude'],
                'longitude' => $request['address']['longitude'],
                'city' => $request['address']['city'] ?? "",
                'country' => $request['address']['country'],
                'gender' => isset($request['gender']) ? $request['gender'] : '0',
                'pincode' => $request['address']['pincode'] ?? "",
                'full_address' => $request['address']['full_address'] ?? "",
                'postal_code' => $request['address']['postal_code'] ?? "",
                'country_iso' => $request['address']['country_iso'] ?? "",
                "house_number" => $request['address']['house_number'] ?? "",
                "street" => $request['address']['street'] ?? "",
                "town_city" => $request['address']['town_city'] ?? "",
                'address_type' => isset($request['address']['address_type']) ? $request['address']['address_type'] : ''
            ]);

            $user->paybase_id = (isset($paybaseUserId)) ? $paybaseUserId : '';

            if (isset($user_adress->id)) {
                $user->selected_address = $user_adress->id;
                $user->save();
            }

            $user->attachRole($role);
            if ($request['role_id'] == 2) {
                // vendor list token
                $sandgrid_list_category_token = config('sandgrid.sandgrid_vendor_list_id');
            } else {
                $sandgrid_list_category_token = config('sandgrid.sandgrid_user_list_id');
            }
            // add user to the sandgrid
            $this->add_user_to_sandgrid($request, $sandgrid_list_category_token);

            $token = $user->createToken('Api access token')->accessToken;
            $this->insertDeviceDetails($token, $user->id);
            $user['token'] = rand(10000, 99999);
            Activation::create(['id_user' => $user->id, 'token' => $user['token'], 'email' => $user['email']]);
            Mail::to($user->email)->send(new Activate($user));
            return (new UserResource($user, $token))->additional([
                // 'currentStatus' => (int) $user->status,
                'status' => 1,
                'message' => trans('api/user.registered_successfully')
            ]);
        } catch (\Exception $ex) {
            // echo $ex->getMessage();
            Log::error($ex);  
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    /**
     * 
     * @param type $reffer_code
     */

    private function createReferalCoupon($reffer_code)
    {
        $referel_user = User::where('refferal', $reffer_code)->first();
        if ($referel_user) {
            $coupon = $this->generate_random_string();
            userCoupon::create([
                'user_id' => $referel_user->id,
                'name' => 'Reward On refer Friend',
                'code' => $coupon,
                'type' => 'Percent',
                'discount' => '',
                'minAmount' => '',
                'maxTotalUse' => 1,
                'totalUsed' => 1,
                'status' => '0',
                'startDateTime' => date('Y-m-d H:i:s'),
                'endDateTime' => date('d-m-Y H:i:s', strtotime(date('Y-m-d H:i:s') . ' +30 days')),
            ]);
        }
    }

    public function uploadImage(Request $request)
    {

        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'avatar_id' => 'required_if:role_id,==,1',
            'profile_image' => 'required_if:role_id,==,2'
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }

        try {
            if ($user->hasRole('vendor')) {
                $image = $request->file('profile_image');
                $extension = $image->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $image->move('images/avatars/', $filename);
                $user->update(['image' => $filename]);
                $img = $user->image ? url('images/avatars/' . $user->image) : '';
                $this->response['data']->profile_image = $img;
                $this->response['status'] = 1;
                $this->response['message'] = trans('api/user.image_uploaded');
                return response($this->response, 200);
            } else {
                $avatar = AvatarImage::find($request['avatar_id']);
                if ($avatar == "") {
                    $this->response['status'] = 0;
                    $this->response['message'] = trans('api/user.avatar_image_not_exist');
                    return response()->json($this->response, 200);
                } else {
                    $user->avtaar_image = $request['avatar_id'];
                    $user->save();
                    return (new AvatarResource($avatar))->additional([
                        'status' => 1,
                        'message' => trans('api/user.avatar_image_changed')
                    ]);
                }
            }
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function uploadVenderImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required',
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        try {
            $image = $request->file('profile_image');
            $extension = $image->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $image->move('images/avatars/', $filename);
            $img = $filename ? url('images/avatars/' . $filename) : '';
            $this->response['data']->profile_image = $img;
            $this->response['data']->image_name = $filename;
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/user.image_uploaded');
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function uploadVenderDoc(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'vender_doc' => 'required',
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        try {

            $image = $request->file('vender_doc');
            $extension = $image->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $image->move('images/doc/', $filename);
            $doc = $filename ? url('images/doc/' . $filename) : '';
            $this->response['data']->vender_doc = $doc;
            $this->response['data']->doc_name = $filename;
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/user.doc_uploaded');
            return response($this->response, 200);
        } catch (\Exception $ex) {
            Log::error($ex);  
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    /**
     * Edit user
     *
     * @return User
     */
    public function editUser(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:45',
            'email' => 'required|string|email|max:255',
            // 'country_code' => 'required',
            'phone' => ['required'],
            'gender' => ['required'],
        ]);

        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        $user_bio = isset($request->bio) ? $request->bio : " ";
        $user->firstname = Input::get('name');
        // $user->phone_country_code = Input::get('country_code');
        $user->phone_number = Input::get('phone');
        $user->gender = Input::get('gender');
        $user->bio =  $user_bio;
        $user->save();
        //if ($user->hasRole('vendor')) {

        $get_address = UserAddresses::where(['user_id' => $user->id])->first();
        $address = array(
            'place_id' => $request['address']['place_id'] ? $request['address']['place_id'] : $get_address->place_id,
            'latitude' => $request['address']['latitude'] ? $request['address']['latitude'] : $get_address->latitude,
            'longitude' => $request['address']['longitude'] ? $request['address']['longitude'] : $get_address->longitude,
            'city' => isset($request['address']['city']) ? $request['address']['city'] : $get_address->city,
            'country' => isset($request['address']['country']) ? $request['address']['country'] : $get_address->country,
            'pincode' => isset($request['address']['pincode']) ? $request['address']['pincode'] : $get_address->pincode,
            'full_address' => isset($request['address']['full_address']) ? $request['address']['full_address'] : $get_address->full_address,
        );
        $get_address->update($address);
        $user->selected_address = $get_address->id;
        $user->save();
        $token = $user->createToken('Api access token')->accessToken;
        $this->insertDeviceDetails($token, $user->id);
        return (new UserResource($user, $token))->additional([
            'status' => 1,
            'message' => trans('api/user.user_updated')
        ]);
    }

    /**
     * Check for user Activation Code
     * @param  array $data
     * @return User
     * */
    public function userActivation(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'otp' => 'required|string|max:45',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        try {
            $token = $check = Activation::where(['token' => $request['otp'], 'id_user' => $request['user_id']])->first();

            if (!is_null($check)) {
                $user = User::find($check->id_user);
                $user->update(['status' => '1']);
                $user->update(['email' => $token->email]);
                $token->delete();
                $message = "Account activated successfully";
                return (new UserResource($user))->additional([
                    'status' => 1,
                    'message' => $message
                ]);
            }
            $this->response['message'] = "Invalid OTP";
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = "Something went wrong";
            return response($this->response, 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string',
            'password' => 'required',
            'role_id' => 'required'
        ]);
        $role = ($request['role_id'] == 2) ? 'vendor' : 'user';
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 404);
        }

        if (empty(Role::where(array("id" => $request['role_id']))->first())) {
            $this->response['message'] = trans('api/user.role_not_exist');
            return response($this->response, 403);
        }
        try {
            $email = strtolower($request->user_name);
            $user = User::where('email', $email)->first();

            if ($user) {
                if (Auth::attempt(['email' => $email, 'password' => request('password')])) {


                    $token = $user->createToken('Api access token')->accessToken;
                    $this->insertDeviceDetails($token, $user->id);
                    if (!$user->hasRole([$role])) {
                        $this->response['message'] = trans('api/user.you_are_not_valid_user');
                        return response()->json($this->response, 403);
                    }
                    if ($user->is_verified == User::not_verified) {
                        return (new UserResource($user, $token))->additional([
                            'currentStatus' => (int) User::pending,
                            'status' => 0,
                            'message' => trans('api/user.not_verified')
                        ]);
                    }
                    if ($role == 'vendor') {
                        if ($user->status == User::pending) {
                            return (new UserResource($user, $token))->additional([
                                'currentStatus' => (int) User::pending,
                                'status' => 0,
                                'message' => trans('api/user.account_review_pending_from_assist')
                            ]);
                        }

                        if ($user->status == User::rejected) {


                            return (new UserResource($user, $token))->additional([
                                'currentStatus' => (int) User::rejected,
                                'status' => 0,
                                'message' => trans('api/user.account_rejected_from_assist')
                            ]);
                        }
                    }
                    if ($user->status == User::not_verified) {
                        return (new UserResource($user, $token))->additional([
                            'currentStatus' => (int) User::not_verified,
                            'status' => 0,
                            'message' => trans('api/user.not_verified')
                        ]);
                    }
                    if ($user->status == User::inActive) {

                        return (new UserResource($user, $token))->additional([
                            'currentStatus' => (int) User::inActive,
                            'status' => 0,
                            'message' => trans('api/user.activate_account_first')
                        ]);
                    }

                    return (new UserResource($user, $token))->additional([
                        'currentStatus' => 1,
                        'status' => 1,
                        'message' => trans('api/user.loggedin_successfully')
                    ]);
                } else {
                    $this->response['message'] = trans('api/user.password_match');
                    return response($this->response, 404);
                }
            } else {
                $this->response['message'] = trans('api/user.user_not_exist');
                return response($this->response, 404);
            }
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            $user->update(['online' => '0']);
            $value = $request->bearerToken();
            $id = (new Parser())->parse($value)->getHeader('jti');
            $deviceDetails = DeviceDetails::where('access_token_id', $id)->get();
            if ($deviceDetails) {
                foreach ($deviceDetails as $deviceDetail) {
                    $deviceDetail->delete();
                }
            }
            $token = $request->user()->tokens->find($id);
            $token->revoke();
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/user.loggedout_successfully');
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function resendOtp(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        try {

            $checkEmailalreadyexist = User::where('email', '=', $request['email'])->first();
            if ($checkEmailalreadyexist) {
                $this->response['message'] = trans('api/user.email_already_exist');
                return response($this->response, 403);
            }

            $user = User::where('email', $request['email'])->first() ? User::where('email', $request['email'])->first() : Auth::User();

            $user['token'] = rand(10000, 99999);

            $check_otp_already_sent = '';


            if ($user->email == $request['email']) {
                $check_otp_already_sent = Activation::where('email', $user->email)->where('id_user', $user->id)->first();
            }
            if ($check_otp_already_sent) {
                $check_otp_already_sent->token = $user['token'];
                $check_otp_already_sent->save();
            } else {
                $check_otp_already_sent = Activation::where('email', $request['email'])->where('id_user', $user->id)->first();
                if ($check_otp_already_sent) {
                    $check_otp_already_sent->token = $user['token'];
                    $check_otp_already_sent->save();
                } else {
                    $check_otp_already_sent = Activation::create(['id_user' => $user->id, 'token' => $user['token'], 'email' => $request['email']]);
                }
            }
            Mail::to($check_otp_already_sent->email)->send(new Activate($user));
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/user.email_otp_sent');
            return response($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    // send otp to user while request to update password
    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'country_code' => 'required',
            'phone_no' => 'required'
        ]);

        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        try {
            $user = User::where('phone_number', $request['phone_no'])->first();
            //            $user = User::where('phone_country_code', $request['country_code'])->where('phone_number', $request['phone_no'])->first();
            if (!$user) {
                $this->response['message'] = "This phone number not exist";
                return response($this->response, 200);
            }
            //$user['password_otp'] = rand(10000, 99999);
            $user->update(['otp' => '4444']);
            $this->response['message'] = trans('api/user.otp_sent');
            $this->response['data'] = (object) array('id' => $user->id);
            $this->response['status'] = 1;
            return response($this->response, 200);
            //$phone_no = $request['phone_no'];
            //            $phone_no = $request['country_code'].$request['phone_no'];
            /* $twilio = new Aloha\Twilio\Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_SMS_FROM_NUMBER'));
              if ($user['password_otp']) {
              if ($twilio->message($phone_no, 'Otp is ' . $user['password_otp'])) {
              $this->response['message'] = trans('api/user.otp_sent');
              $this->response['data'] = (object) array('id' => $user->id);
              $this->response['status'] = 1;
              return response($this->response, 200);
              /* return (new UserResource($user))->additional([
              'status' => 1,
              'message' => "Otp sent on your phone Please check your message."
              ]);
              }
              } */
            return (new UserResource($user))->additional([
                'status' => 1,
                'message' => "Otp sent on your phone Please check your message."
            ]);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    // Match otp to user while request to update password
    public function checkOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        try {
            $user = User::where('id', $request['id'])->first();
            if (!$user) {
                $this->response['message'] = trans('api/user.user_not_exist');
                return response($this->response, 200);
            }
            // if ($user->is_verified == User::verified) {
            //     $this->response['status'] = 1;
            //     $this->response['message'] = trans('api/user.user_already_verified');
            //     return response()->json($this->response, 200);
            // }

            $user = User::where('otp', $request['otp'])->where('id', $request['id'])->first();
            if (!$user) {
                $this->response['message'] = trans('api/user.invalid_otp');
                return response($this->response, 200);
            }

            $this->response['message'] = trans('api/user.otp_matched');
            $this->response['data'] = (object) array('id' => $user->id);
            $this->response['status'] = 1;
            return response($this->response, 200);
        } catch (\Exception $ex) {

            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function verifyEmailOtp(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        try {

            $user = Activation::where('token', $request['otp'])->where('id_user', $request['id'])->first();
            if (!$user) {
                $this->response['message'] = trans('api/user.invalid_otp');
                return response($this->response, 200);
            }
            DB::table('users')->where('id', $user->id_user)->update(['email' => $user->email]);
            $user->delete();
            $user = User::find($user->id_user);
            $token = $user->createToken('Api access token')->accessToken;
            $this->insertDeviceDetails($token, $request['id']);
            return (new UserResource($user, $token))->additional([
                'status' => 1,
                'message' => trans('api/user.otp_matched')
            ]);
        } catch (\Exception $ex) {

            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    // update password

    public function updatePassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'password' => ['required'],
            'old_password' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        $usr = Auth::User();
        try {
            $user = User::where('id', $usr->id)->first();
            if (!$user) {
                $this->response['message'] = trans('api/user.user_not_found');
                return response($this->response, 200);
            }


            $old_password = $request['old_password'];
            if ($user) {
                if (!Hash::check($old_password, $user->password)) {
                    $this->response['message'] = trans('api/user.old_password_not_matched');
                    return response($this->response, 200);
                }
            }
            $user->update(['password' => Hash::make($request['password'])]);
            $this->response['message'] = trans('api/user.password_updated');
            $this->response['status'] = 1;
            return response()->json($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function updateForgetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }

        try {
            $user = User::where('id', $request['id'])->first();
            if (!$user) {
                $this->response['message'] = trans('api/user.user_not_exist');
                return response($this->response, 200);
            }
            $user->update(['password' => Hash::make($request['password'])]);
            $this->response['message'] = trans('api/user.password_updated');
            $this->response['status'] = 1;
            return response()->json($this->response, 200);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function updateEmail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', ValidationRule::unique('users')->ignore(Auth::user()->id)],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        $user = Auth::user();
        try {
            $user = User::where('email', $user->email)->first();
            if (!$user) {
                $this->response['status'] = 1;
                $this->response['message'] = trans('api/user.email_not_exist');
                return response($this->response, 200);
            }

            $user['token'] = rand(10000, 99999);
            $check_otp_already_sent = Activation::where('email', $request['email'])->where('id_user', $user->id)->first();

            if ($check_otp_already_sent) {
                $check_otp_already_sent->update(['token' => $user['token']]);
                $check_otp_already_sent->save();
            } else {
                Activation::create(['id_user' => $user->id, 'token' => $user['token'], 'email' => $request['email']]);
            }
            Mail::to($request['email'])->send(new UpdateEmail($user));
            return (new UserResource($user))->additional([
                'status' => 1,
                'message' => trans('api/user.otp_sent_on_email')
            ]);
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function getRoles(PushNotification $pushNotification, ScheduleService $scheduleService)
    {
        $roles = Role::where('level', 4)->get();
        return (new RolesResourceCollection($roles))->additional([
            'status' => 1,
            'message' => "Get record succesfully"
        ]);
    }

    public function phoneOtp(Request $request)
    {

        $validator = Validator::make($request->all(), [
            // 'country_code' => 'required',    
            'phone_no' => ['required'],
            'user_id' => ['required']
        ]);
        if ($validator->fails()) {

            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        try {
            // $user_otp = phoneOtp::where('phone_no', $request['phone_no'])->first();
            //            $user_otp = phoneOtp::where('country_code', $request['phone_country_code'])->where('phone_no', $request['phone_no'])->first();
            // if (isset($user_otp->is_verified) && $user_otp->is_verified != 0) {
            //     $this->response['message'] = trans('api/user.phone_already_registered');
            //     return response()->json($this->response, 409);
            // }
            // $otp = rand(10000, 99999);
            $otp = 4444;
            // $phone_no = $request['phone_no'];
            //$phone_no = $request['phone_country_code'].$request['phone_no'];
            // $twilio = new Aloha\Twilio\Twilio(env('TWILIO_SID'), env('TWILIO_TOKEN'), env('TWILIO_SMS_FROM_NUMBER'));
            $user = User::where('id', $request->user_id)->first();
            if (!$user) {
                $this->response['message'] = trans('api/user.user_not_found');
                $this->response['status'] = 0;
                return response()->json($this->response, 404);
            }
            if ($user) {
                // if ($twilio->message($phone_no, 'Otp is ' . $otp)) {
                // $user_otp->update(['otp' => $otp]);
                if ($user->update(['otp' => $otp])) {
                    $this->response['message'] = trans('api/user.otp_sent');
                    $this->response['status'] = 1;
                    return response()->json($this->response, 200);
                } else {
                    $this->response['message'] = trans('api/user.otp_not_sent');
                    $this->response['status'] = 1;
                    return response()->json($this->response, 401);
                }
            }
            // if ($twilio->message($phone_no, 'Otp is ' . $otp)) {
            //     $user = phoneOtp::create([
            //'phone_country_code' => $request['phone_country_code'];
            //         'phone_no' => $request['phone_no'],
            //         'otp' => $otp,
            //     ]);
            //     if (!$user) {
            //         $this->response['message'] = trans('api/user.otp_not_sent');
            //         return response()->json($this->response, 401);
            //     }
            //     $this->response['message'] = trans('api/user.otp_sent');
            //     $this->response['status'] = 1;
            //     return response()->json($this->response, 200);
            // }
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response()->json($this->response, 401);
        }
    }

    public function changeVendorOnlineStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => ['required'],
            ]);
            if ($validator->fails()) {

                $this->response['message'] = $validator->errors()->first();
                return response()->json($this->response, 401);
            }
            $user = Auth::user();
            if (!$user) {
                $this->response['message'] = trans('api/user.user_not_exist');
                $this->response['status'] = 0;
                return response()->json($this->response, 404);
            }
            if (!$user->status) {
                $this->response['message'] = trans('api/user.user_is_inactive');
                $this->response['status'] = 0;
                return response()->json($this->response, 404);
            }
            $status = $request['status'];
            $user->update(['online' => $status]);
            $msg = trans('api/user.you_are_offline');
            if ($status == '1') {
                $msg = trans('api/user.you_are_online');
            }
            $this->response['message'] = $msg;

            $this->response['status'] = 1;
            $this->response['data']->status = (int) $status;

            return response()->json($this->response, 200);
            /* return (new UserResource($user))->additional([
              'status' => 1,
              'message' => trans('api/user.online_status')
              ]); */
        } catch (Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function testNotification()
    {
        $user = Auth::User();
        $notificationMessage = 'this is test msg';
        if (Notification::createNotification(12, 'hiiiii', 'suraj', $notificationMessage, $user->id)) {
            echo 'sent';
        } else {
            echo 'not sent';
        }
    }

    public function updatePhoneNumber(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_no' => ['required'],
                'otp' => ['required'],
            ]);
            if ($validator->fails()) {
                $this->response['status'] = 0;
                $this->response['message'] = $validator->errors()->first();
                return response()->json($this->response, 401);
            }

            $user = Auth::User();
            $matchOtp = User::where(['id' => $user->id, 'otp' => $request->otp])->first();
            if (!$matchOtp) {
                $this->response['message'] = trans('api/user.invalid_otp');
                $this->response['status'] = 0;
                return response()->json($this->response, 404);
            } else {
                $user->phone_number = $request->phone_no;
                $user->save();
                $this->response['message'] = trans('api/user.phone_updated_success');
                $this->response['status'] = 1;
                return response()->json($this->response, 200);
            }
        } catch (Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
    }

    public function updateUserCardDetail(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'card_id' => 'required'
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 200);
        }
        $user->card_id = $request['card_id'];
        $user->card_number_suffix = $request['card_number_suffix'];
        $user->save();

        $data['card_id'] = ($user->card_id) ? $user->card_id : '';
        $data['card_number_suffix'] = ($user->card_number_suffix) ? $user->card_number_suffix : '';

        $this->response['message'] = trans('api/user.card_saved');
        $this->response['data'] = $data;
        $this->response['status'] = 1;
        return response()->json($this->response, 200);
    }

    public function test(Request $request)
    {
        if ($request['role_id'] == 2) {
            // vendor list token
            $sandgrid_list_category_token = config('sandgrid.sandgrid_vendor_list_id');
        } else {
            $sandgrid_list_category_token = config('sandgrid.sandgrid_user_list_id');
        }
        // add user to the sandgrid
        $this->add_user_to_sandgrid($request, $sandgrid_list_category_token);
    }
}

// user controller 
