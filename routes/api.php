<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => ['api']], function () {
    Route::post('auth/login', 'AuthController@authenticate');
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'API\UsersController@register');
Route::post('accountActivation', 'API\UsersController@userActivation');
Route::post('login', 'API\UsersController@login');
Route::post('forgetpassword', 'API\UsersController@forgetPassword');
Route::post('checkotp', 'API\UsersController@checkOtp');
Route::post('updateforgetpassword', 'API\UsersController@updateForgetPassword');
Route::get('getRoles', 'API\UsersController@getRoles');
Route::post('phoneOtp', 'API\UsersController@phoneOtp');
Route::post('uploadVenderImage', 'API\UsersController@uploadVenderImage');
Route::post('uploadVenderDoc', 'API\UsersController@uploadVenderDoc');

Route::post('checkPhoneOtp', 'API\UsersController@checkPhoneOtp');
Route::get('listBanners', 'API\ServicesController@listBanners');
Route::get('listServices', 'API\ServicesController@index');
Route::get('autoCancelBooking', 'API\BookingController@autoCancelBooking');
Route::get('payment', 'API\BraintreeTokenController@payment');
Route::post('sociallogin', 'API\UsersController@socialLogin');

Route::post('vendor-report', 'API\vendorController@vendorReport')->middleware('auth:api');

Route::middleware('auth:api', 'checkuseractivation:api')->group(function () {
    Route::get('braintreeToken', 'API\BraintreeTokenController@index');
    Route::get('getServices', 'API\ServicesController@get_all_services');
    Route::get('logout', 'API\UsersController@logout');
    Route::post('addPaypalCredential', 'API\UsersController@addPaypalCredential');
    Route::post('uploadImage', 'API\UsersController@uploadImage');
    Route::post('sendEmailOtp', 'API\UsersController@resendOtp');
    Route::post('verifyEmailOtp', 'API\UsersController@verifyEmailOtp');
    Route::post('editUser', 'API\UsersController@editUser');
    Route::post('updateEmail', 'API\UsersController@updateEmail');
    Route::post('updatepassword', 'API\UsersController@updatePassword');
    Route::post('bookService', 'API\BookingController@bookService');
    Route::post('testBookService', 'API\BookingController@testBookService');
    Route::post('acceptBooking', 'API\BookingController@acceptBooking');
    Route::post('checkCouponCode', 'API\ServicesController@checkCouponCode');
    Route::get('getAddress', 'API\UserAddressesController@getAddress');
    Route::post('addAddress', 'API\UserAddressesController@addAddress');
    Route::post('editAddress', 'API\UserAddressesController@editAddress');
    Route::post('getSlots', 'API\venderSlotController@getSlots');
    Route::post('addVenderSlot', 'API\venderSlotController@addVenderSlot');
    Route::post('selectedAddress', 'API\UserAddressesController@selectedAddress');
    Route::post('deleteAddress', 'API\UserAddressesController@destroy');
    Route::post('updateAddress', 'API\UserAddressesController@updateAddress');
    Route::post('changeVendorOnlineStatus', 'API\UsersController@changeVendorOnlineStatus');
    Route::post('updateOrderStatus', 'API\BookingController@updateOrderStatus');
    Route::post('getInvoiceDetails', 'API\BookingController@getInvoiceDetails');
    Route::post('addReview', 'API\BookingController@addReview');
    Route::get('getNotifications', 'API\NotificationController@getNotifications');
    Route::post('getFilters', 'API\NotificationController@getFilters');
    Route::post('getOrders', 'API\BookingController@getOrders');
    Route::post('getBookingDetail', 'API\BookingController@getBookingDetail');
    Route::post('testNotification', 'API\UsersController@testNotification');
    Route::post('cancelBooking', 'API\BookingController@cancelBooking');
    Route::post('rescheduleBooking', 'API\BookingController@rescheduleBooking');
    Route::post('getVenderBookingNotifications', 'API\BookingController@getVenderBookingNotifications');
    Route::post('vendorBookingsList', 'API\BookingController@vendorBookingsList');
    Route::post('getAppointments', 'API\BookingController@getAppointments');
    Route::post('getRescheduleData', 'API\BookingController@getRescheduleData');
    Route::post('extentionRequestToUser', 'API\BookingController@extentionRequestToUser');
    Route::post('updateExtendTimeRequest', 'API\BookingController@updateExtendTimeRequest');
    Route::post('sendMessage', 'API\ChatController@sendMessage');
    Route::get('listChats', 'API\ChatController@listChats');
    Route::get('getChat', 'API\ChatController@get_chat');
    Route::post('getBookedSlots', 'API\venderSlotController@getBookedSlots');
    Route::post('getHistory', 'API\HistoryController@getHistory');
    Route::post('enquiry', 'API\NotificationController@enquiry');
    Route::post('update-phone-number', 'API\UsersController@updatePhoneNumber');
    Route::get('get-coupons', 'API\ServicesController@getCoupon');
    route::post('get-all-vendor', 'API\vendorController@getAllVendors');
    Route::post('get-user-info', 'API\vendorController@getUserInfo');
    Route::post('get-reviews', 'API\vendorController@getReviews');
    Route::post('add-favorite', 'API\vendorController@addFavorite');
    Route::post('get-favorite-vendors', 'API\vendorController@vandorFavoriteList');
    Route::post('createStandardVendorAccount', 'API\vendorController@createStandardVendorAccount');
    Route::post('coupon-applied', 'API\ServicesController@couponApplied');
    Route::post('purchase-subscription', 'API\PaymentController@PurchaseSubscription');
    Route::get('get-subscription', 'API\PaymentController@GetSubscription');
    Route::post('updateUserCardDetail', 'API\UsersController@updateUserCardDetail');
    Route::post('notify-me', 'API\BookingController@notifyMe');

    Route::post('check-service-availability', 'API\ServicesController@checkServiceAvailability');
});
