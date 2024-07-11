<?php

namespace App\Traits;

use Lcobucci\JWT\Parser;
use Auth;
use Illuminate\Support\Facades\Request;
use App\DeviceDetails;
use App\User;

trait ApiUserTrait
{
    public function insertDeviceDetails($token, $userId = '')
    {
        $user = ($userId) ? User::find($userId) : Auth::User();
        $user_type = '';
        if ($user->hasRole('vendor')) {
            $user_type = '2';
        } else {
            $user_type = '1';
        }
        $device_id =  Request::header('device-id') ?  Request::header('device-id') : NULL;

        // delete all the previous records in device details table
        $Device_details = DeviceDetails::where(['user_id' => $user->id, 'device_id' => $device_id])->delete();
        $tokenId = (new Parser())->parse($token)->getHeader('jti');
        DeviceDetails::create([
            'access_token_id' => $tokenId,
            'device_token' => Request::header('device-token'),
            'device_id' => Request::header('device-id'),
            'build_version' => Request::header('build-version'),
            'platform' => Request::header('platform'),
            'build' => Request::header('build'),
            'user_id' => $user->id,
            'user_type' => $user_type
        ]);
        return true;
    }

    public function userDetailsResponse($userId, $token = "")
    {
        $user = User::find($userId);
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['token'] = $token;
        $data['profile_image'] = $user->avatar;
        $data['is_verified'] = $user->is_activated;
        $data['phone_number'] = $user->phone_number;
        return $data;
    }
}
