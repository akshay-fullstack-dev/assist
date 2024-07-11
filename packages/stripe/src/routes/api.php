<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'api/v1', 'middleware' => 'auth:api', 'namespace' => 'Intersoft\Stripe\Http\Controllers'], function () {
    Route::get('accountLinked', 'StripeController@accountLinked');
    Route::get('customerExists', 'StripeController@customerExists');
    Route::post('linkAccount', 'StripeController@linkAccount');
    Route::post('createCustomer', 'StripeController@createCustomer');
});
