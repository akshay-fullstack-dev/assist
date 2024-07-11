<?php

return [
    'ADMIN_IMAGE_ROOT' => env('APP_URL') . '/uploads/admin/',
    'ADMIN_IMAGE_PATH' => base_path() . '/uploads/admin/',
    'LOGO_ROOT' => env('APP_URL') . '/uploads/logo/',
    'LOGO_PATH' => base_path() . '/uploads/logo/',
    'USER_IMAGE_ROOT' => env('APP_URL') . '/uploads/user/',
    'USER_IMAGE_PATH' => base_path() . '/uploads/user/',
    'USER_DOCUMENTS_PATH' => base_path() . '/uploads/documents/',
    'EQUIPMENT_IMAGES' => base_path() . '/assets/equipments/',
    'SERVICE_IMAGES' => base_path() . '/assets/services/',
    'CATEGORY_IMAGES' => base_path() . '/assets/category/',
    'CURRENCY_SYMBOL' => '£',
    'VENDOR_RATINGS_FOR_PRO' => 200,
];
