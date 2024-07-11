<?php

namespace App\Http\Controllers\API;

use App\UserAddresses;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Address as addressResource;
use App\Http\Resources\AddressCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\User as UserResource;
use App\User;

class UserAddressesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    const totalRow = 20;

    protected $response = [
        'status' => 0,
        'message' => '',
    ];

    public function __construct()
    {
        $this->response['data'] = new \stdClass();
    }

    public function getAddress()
    {

        $userId = Auth::User();
        $userAddresses = UserAddresses::where(['user_id' => $userId->id])->paginate(self::totalRow);
        return (new AddressCollection($userAddresses))->additional([
            'status' => 1,
            'message' => 'All adresses'
        ]);
    }

    public function selectedAddress(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'address_id' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $user = Auth::User();

        $check_adress_exist = UserAddresses::where(['id' => $request['address_id'], 'user_id' => $user->id])->first();
        if (!$check_adress_exist) {
            $this->response['message'] = 'No adress found';
            return response()->json($this->response, 401);
        }
        $user->selected_address = $request['address_id'];
        $u = $user->save();
        if ($u) {
            return (new addressResource($check_adress_exist))->additional([
                'status' => 1,
                'message' => trans('api/user.default_address_successfully')
            ]);
        }
    }

    public function addAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'place_id' => ['required'],
            'latitude' => ['required'],
            'longitude' => ['required'],
            'full_address' => ['required'],
            'phone' => ['required']
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $user = Auth::User();
        $address = UserAddresses::create([
            'user_id' => $user->id,
            'place_id' => $request['place_id'],
            'latitude' => $request['latitude'],
            'longitude' => $request['longitude'],
            'phone' => isset($request['phone']) ? $request['phone'] : '',
            'city' => isset($request['city']) ? $request['city'] : '',
            'country' => isset($request['country']) ? $request['country'] : '',
            'pincode' => isset($request['pincode']) ? $request['pincode'] : '',
            'full_address' => isset($request['full_address']) ? $request['full_address'] : '',
            'address_type' => isset($request['address_type']) ? $request['address_type'] : 'other'
        ]);

        if ($user->selected_address == 0) {
            $user->selected_address = $address->id;
            $user->save();
        }

        return (new addressResource($address))->additional([
            'status' => 1,
            'message' => trans('api/user.adress_added_successfully')
        ]);
    }

    public function updateAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $get_address = UserAddresses::where(['id' => $request['id']])->first();

        $address = array(
            'place_id' => $request['place_id'] ? $request['place_id'] : $get_address->place_id,
            'latitude' => $request['latitude'] ? $request['latitude'] : $get_address->latitude,
            'longitude' => $request['longitude'] ? $request['longitude'] : $get_address->longitude,
            'phone' => isset($request['phone']) ? $request['phone'] : $get_address->phone,
            'city' => isset($request['city']) ? $request['city'] : $get_address->city,
            'country' => isset($request['country']) ? $request['country'] : $get_address->country,
            'pincode' => isset($request['pincode']) ? $request['pincode'] : $get_address->pincode,
            'full_address' => isset($request['full_address']) ? $request['full_address'] : $get_address->full_address,
            'address_type' => $request['address_type']
        );

        if ($get_address->update($address)) {
            $get_addres = UserAddresses::where(['id' => $request['id']])->first();
            return (new addressResource($get_addres))->additional([
                'status' => 1,
                'message' => trans('api/user.adress_updated_successfully')
            ]);
        }
    }

    public function destroy(Request $request)
    {
        $sel_address = 0;
        $user = UserAddresses::where(['id' => $request['address_id']])->first();
        $selected_address = User::where(['id' => $user->user_id])->first();
        $sel_address = $selected_address->selected_address;
        UserAddresses::destroy($request['address_id']);

        if ($request['address_id'] == $selected_address->selected_address) {
            $other_adress = UserAddresses::where(['user_id' => $selected_address->id])->first();
            if (isset($other_adress->id) && $other_adress->id != '') {
                $selected_address->selected_address = $other_adress->id;
                $sel_address = $other_adress->id;
                $new_selected_adress = $selected_address->save();
            } else {
                $selected_address->selected_address = 0;
                $sel_address = 0;
                $new_selected_adress = $selected_address->save();
            }
        }
        $this->response['data'] = array('selectedAddress' => $sel_address);
        $this->response['status'] = 1;
        $this->response['message'] = trans('api/user.adress_deleted_successfully');
        return response()->json($this->response, 200);
    }
}
