<?php

namespace App\Services;

use App\DeviceDetails;
use Illuminate\Support\Facades\Auth;

class PushNotification
{

    public function __construct()
    {
    }

    private function androidPushNotifiction($message, $title, $deviceToken, $moreData = [])
    {
        $registrationIds = (is_array($deviceToken)) ? $deviceToken : ["$deviceToken"];

        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $data = [
            'title' => $title,
            'body' => $message,
        ];

        $fcmNotification = [
            'registration_ids' => $registrationIds, //multple token array
            "data" => $moreData,
            "notification" => $data,
        ];


        $headers = [
            'Authorization: key=' . config('services.fcm_token'),
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function iphonePushNotifiction($message, $title, $deviceToken, $moreData = [])
    {
        $registrationIds = (is_array($deviceToken)) ? $deviceToken : ["$deviceToken"];

        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $data = [
            'title' => $title,
            'body' => $message,
            'priority' => '"high"',
        ];

        $fcmNotification = [
            'registration_ids' => $registrationIds, //multple token array
            "data" => $moreData,
            "notification" => $data,
        ];


        $headers = [
            'Authorization: key=' . config('services.fcm_token'),
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        if ($result) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function sendNotification($data, $userId)
    {

        $androidDeviceTokens = DeviceDetails::where('user_id', $userId)->AndroidTokens()->get()->toArray();
        $iphoneTokens = DeviceDetails::where('user_id', $userId)->IosToken()->get()->toArray();
        $status = '';
        if (!empty($androidDeviceTokens)) {
            $status = $this->androidPushNotifiction($data['message'], $data['title'], array_column($androidDeviceTokens, 'device_token'), $data['data']);
        }
        if (!empty($iphoneTokens)) {
            $status = $this->iphonePushNotifiction($data['message'], $data['title'], array_column($iphoneTokens, 'device_token'), $data['data']);
        }

        return $status;
    }
}
