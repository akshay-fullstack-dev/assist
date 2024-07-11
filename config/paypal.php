<?php

return [
    'user_id' => env('X_PAYPAL_SECURITY_USERID', ""),
    'password' => env('X_PAYPAL_SECURITY_PASSWORD', ""),
    'signature' => env('X_PAYPAL_SECURITY_SIGNATURE', ""),
    'aplication_id' => env('X_PAYPAL_APPLICATION_ID', ""),
    'base_url' => env('PAYPAL_BASE_URL',""),
];
