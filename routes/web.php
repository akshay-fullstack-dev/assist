<?php
/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | This file is where you may define all of the routes that are handled
  | by your application. Just tell Laravel the URIs it should respond
  | to using a Closure or controller method. Build something great!
  |
 */

/** ------------------------------------------
 *  Admin Routes
 *  ------------------------------------------
 */
Route::get('/',  'Admin\LoginController@getIndex')->middleware('guest.admin');
Route::group(array('prefix' => 'admin'), function () {

    Route::get('/', array('middleware' => 'guest.admin', 'uses' => 'Admin\LoginController@getIndex'));
    Route::get('logout', array('uses' => 'Admin\LoginController@doLogout'));
    Route::post('login', array('uses' => 'Admin\LoginController@doLogin'));

    // Password Reset Routes...
    Route::get('password/reset', array('uses' => 'Admin\ForgotPasswordController@showLinkRequestForm', 'as' => 'admin.password.email'));
    Route::post('password/email', array('uses' => 'Admin\ForgotPasswordController@sendResetLinkEmail', 'as' => 'admin.password.email'));
    Route::get('password/reset/{token}', array('uses' => 'Admin\ResetPasswordController@showResetForm', 'as' => 'admin.password.reset'));
    Route::post('password/reset', array('uses' => 'Admin\ResetPasswordController@reset', 'as' => 'admin.password.reset'));

    //after login
    Route::group(array('middleware' => 'auth.admin'), function () {

        Route::get('dashboard', 'Admin\DashboardController@index');
        #Settings Management
        Route::resource('settings', 'Admin\SettingsController');

        #paypal settings management
        Route::resource('paypalsettings', 'Admin\PaypalSettingsController');
        Route::get('equipments/equipmentData', 'Admin\EquipmentController@EquipmentData');
        Route::resource('equipments', 'Admin\EquipmentController');


        #currency management
        Route::get('currency/CurrencyData', 'Admin\CurrencyController@getCurrencyData');
        Route::post('currency/changeStatus', 'Admin\CurrencyController@changeCurrencyStatus');
        Route::resource('currency', 'Admin\CurrencyController');

        #payment settings management
        Route::resource('paymentsettings', 'Admin\PaymentSettingsController');

        #Admin Profile Management
        Route::resource('profile', 'Admin\ProfileController');

        #Admin password change
        Route::get('password/change', array('uses' => 'Admin\ProfileController@changePassword', 'as' => 'admin.password.change'));
        Route::post('password/change', array('uses' => 'Admin\ProfileController@updatePassword', 'as' => 'admin.password.change'));

        #Services Management
        Route::get('services/ServicesData', 'Admin\ServicesController@getServicesData');
        Route::post('services/changeStatus', 'Admin\ServicesController@changeServiceStatus');
        Route::resource('services', 'Admin\ServicesController');

        Route::post('servicesCategory/changeStatus', 'Admin\ServiceCategoryController@changeServiceStatus');
        Route::get('listCategory', 'Admin\ServiceCategoryController@index')->name('listCategory');
        Route::get('addCategory', 'Admin\ServiceCategoryController@create')->name('addCategory');
        Route::post('saveCategory', 'Admin\ServiceCategoryController@saveCategory')->name('saveCategory');
        Route::get('editCategory/{id}', 'Admin\ServiceCategoryController@editCategory')->name('editCategory');
        Route::post('updateCategory', 'Admin\ServiceCategoryController@updateCategory')->name('updateCategory');
        Route::get('deleteCategory/{id}', 'Admin\ServiceCategoryController@deleteCategory')->name('deleteCategory');

        //Route::resource('services','Admin\ServiceCategoryController');
        #Booking Management
        Route::get('booking/export', 'Admin\BookingController@export');
        Route::any('booking/search', array('uses' => 'Admin\BookingController@search', 'as' => 'admin.booking.search'));
        Route::post('booking/changeStatus', 'Admin\BookingController@changeBookingStatus');
        Route::resource('booking', 'Admin\BookingController');
        Route::get('booking-report', 'Admin\ReportingController@getBookigReport');
        Route::post('filtered-booking-report', 'Admin\ReportingController@getFilteredBookigReport');

        #Transaction Management
        Route::get('transaction/export', 'Admin\TransactionController@export')->name('admin.transaction.export');
        Route::any('transaction/search', array('uses' => 'Admin\TransactionController@search', 'as' => 'admin.transaction.search'));
        Route::resource('transaction', 'Admin\TransactionController');


        #agency Management
        Route::resource('agencies', 'Admin\AgencyController');
        Route::post('agency/changeStatus', 'Admin\AgencyController@changeAgencyStatus');
        Route::post('agency/reject', 'Admin\AgencyController@rejectAgency');
        Route::get('agency/AgencyData', 'Admin\AgencyController@getAgencyData');


        #User #vendor Management
        Route::post('users/updateCredit', 'Admin\UserController@updateCredit');
        Route::get('users/UserData', 'Admin\UserController@getUserData');
        Route::get('vendors/VendorData', 'Admin\VendorController@getVendorData');
        Route::post('users/changeStatus', 'Admin\UserController@changeUserStatus');
        Route::get('users/show/{id}', 'Admin\UserController@show');
        //Route::resources('users/', 'Admin\UserController');

        Route::resource('users', 'Admin\UserController');
        Route::resource('vendors', 'Admin\VendorController');
        Route::get('vendors/show_vendor/{id}', 'Admin\VendorController@show_vendor');
        Route::post('vendor/reject', 'Admin\VendorController@rejectVendor');
        Route::post('venderService/changeStatus', 'Admin\VendorController@changeVenderServiceStatus');
        Route::post('venderService/addVenderService', 'Admin\VendorController@addVenderService');
        Route::post('venderService/delete', 'VenderServiceController@destroy');
        Route::post('vendorSlots/delete', 'Admin\VendorController@deleteVendorSlot');

        Route::resource('users.booking', 'Admin\BookingController');
        Route::resource('users.transaction', 'Admin\TransactionController');

        #chat Management
        Route::get('chatboard/history/{id}', 'Admin\ChatController@history');
        Route::get('chatboard/{id}', 'Admin\ChatController@index');
        Route::post('chatboard/store', 'Admin\ChatController@store');
        Route::post('chatboard/notificationCount', 'Admin\ChatController@getNotificationCount');
        Route::resource('chatboard', 'Admin\ChatController');

        #Enquiry Management
        Route::get('enquiry/EnquiryData', 'Admin\EnquiryController@getEnquiryData');
        Route::post('enquiry/changeStatus', 'Admin\EnquiryController@changeEnquiryStatus');
        Route::resource('enquiry', 'Admin\EnquiryController');

        #Notifications Management
        Route::get('notifications/{type}', 'Admin\NotificationController@getNotifications');
        Route::get('createnotification', 'Admin\NotificationController@createNotification');
        Route::post('sendnotification', 'Admin\NotificationController@sendNotification');

        Route::get('slots/SlotData', 'Admin\SlotController@getSlotData');
        Route::resource('slots', 'Admin\SlotController');
        Route::post('slots/update', 'Admin\SlotController@update');
        Route::post('slots/checkOverlappingSlots', 'Admin\SlotController@checkOverlappingSlots');

        #Vender Services
        Route::get('editVenderService/{id}', 'VenderServiceController@edit')->name('editVenderService');
        Route::get('editVender/{id}', 'Admin\VendorController@edit')->name('editVender');
        Route::any('updateVenderService/{id}', 'VenderServiceController@update')->name('updateVenderService');

        #Coupons
        Route::get('coupons/couponData', 'Admin\CouponController@getCouponData');
        Route::post('coupons/changeStatus', 'Admin\CouponController@changeCouponStatus');
        Route::resource('coupons', 'Admin\CouponController');

        #Banners
        Route::get('slider/sliderData', 'Admin\SliderController@getSliderData');
        Route::post('slider/changeStatus', 'Admin\SliderController@changeBannerStatus');
        Route::resource('slider', 'Admin\SliderController');

        Route::get('get-booking-chat/{booking_id}', 'Admin\ChatController@getBookingChat');


        // service frequency 
        Route::POST('service/frequency/delete/{id}', 'Admin\ServiceFrequencyController@destroy');
        Route::resource('service/frequency', 'Admin\ServiceFrequencyController');
        Route::get('service/frequency-data', 'Admin\ServiceFrequencyController@frequencyData');
    });
});


/** ------------------------------------------
 *  Frontend Routes
 *  ------------------------------------------
 */
//'Frontend\UserController@create'

Route::group(array('prefix' => 'agency'), function () {

    Route::get('register', 'Frontend\UserController@create');
    Route::get('login', 'Frontend\LoginController@index');
    Route::resource('store', 'Frontend\UserController');
    Route::post('doLogin', 'Frontend\LoginController@doLogin');
});

Route::group(array('prefix' => 'agency', 'middleware' => 'auth.user'), function () {


    Route::get('password/change', array('uses' => 'Frontend\UserController@changePassword', 'as' => 'agency.password.change'));
    Route::post('password/change', array('uses' => 'Frontend\UserController@updatePassword', 'as' => 'agency.password.change'));

    // Route::get('dashboard', 'Frontend\DashboardController@index');
    Route::get('users/export', 'Frontend\UserController@export');
    Route::get('booking/export/{id}', 'Frontend\BookingController@export');
    Route::any('booking/search', array('uses' => 'Frontend\BookingController@search', 'as' => 'booking.search'));

    Route::resource('booking', 'Frontend\BookingController');
    Route::get('logout', 'Frontend\LoginController@doLogout');
    Route::get('profile', 'Frontend\UserController@agencyProfile');
    Route::get('allUsers', 'Frontend\UserController@agencyEmployess');
    Route::resource('users', 'Frontend\UserController');
    Route::resource('document', 'Frontend\DocumentController');
    Route::get('addUsers', 'Frontend\UserController@addUser');
    Route::post('storeUser', 'Frontend\UserController@storeUser');
    Route::get('editUser/{id}', 'Frontend\UserController@editUser');
    Route::get('deleteUser/{id}', 'Frontend\UserController@destroy');
    Route::post('updateUser/{id}', 'Frontend\UserController@updateUser');
    Route::get('listBooking/{id}', 'Frontend\BookingController@index');
    Route::get('empBooking/{id}', 'Frontend\BookingController@getEmployeeBooking');
    Route::get('dashboard', 'Frontend\ReportingController@getBookigReport');
    Route::post('filtered-booking-report', 'Frontend\ReportingController@getFilteredBookigReport');
    Route::match(['get', 'post'], 'bookings-list', 'Frontend\BookingController@bookingList');
});


Route::get('/agency', 'Frontend\HomeController@index');

#before login
//Route::get('/', 'Frontend\HomeController@index');

Route::post('contact', array('uses' => 'Frontend\HomeController@submitEnquiry', 'as' => 'contact'));

#login user
//Route::post('login', array('uses' => 'Frontend\LoginController@doLogin', 'as' => 'frontend.login'));
// Password Reset Routes...
Route::get('password/reset', array('uses' => 'Frontend\ForgotPasswordController@showLinkRequestForm', 'as' => 'password.email'));
Route::post('password/email', array('uses' => 'Frontend\ForgotPasswordController@sendResetLinkEmail', 'as' => 'password.email'));
Route::get('password/reset/{token}', array('uses' => 'Frontend\ResetPasswordController@showResetForm', 'as' => 'password.reset'));
Route::post('password/reset', array('uses' => 'Frontend\ResetPasswordController@reset', 'as' => 'password.reset'));

//after login
Route::group(array('middleware' => 'auth.user'), function () {

    Route::get('dashboard', 'Frontend\DashboardController@index');
    //    Route::get('profile', 'Frontend\UserController@index');
    //    
    //    #user password change
    //    Route::get('password/change', array('uses' => 'Frontend\UserController@changePassword', 'as' => 'password.change'));
    //    Route::post('password/change', array('uses' => 'Frontend\UserController@updatePassword', 'as' => 'password.change'));
    //    
    //    #logout user
    //    Route::get('logout', 'Frontend\LoginController@doLogout');
    //    
    //    #chat
    //    Route::get('chat/show', 'Frontend\ChatController@show');
    //    Route::post('chat/store', 'Frontend\ChatController@store');
    //    Route::get('chat', 'Frontend\ChatController@index');
    //    
    //    #reservation
    //    Route::get('reservation/{id}/{day}', 'Frontend\ReservationController@getSpots');
    //    Route::get('getServices', 'Frontend\ReservationController@getServices');
    //    Route::resource('reservation', 'Frontend\ReservationController');
    //    
    //    #booking
    ////    Route::get('booking/export', 'Frontend\BookingController@export');
    ////    Route::any('booking/search', array('uses' => 'Frontend\BookingController@search', 'as' => 'booking.search'));
    ////    Route::resource('booking', 'Frontend\BookingController');
    //    
    //    #buy credit
    //    Route::get('credit', 'Frontend\PaypalController@index');
    //    Route::get('credit/paypal', 'Frontend\PaypalController@getPaypal');
    //    Route::post('credit/paypal', array('uses' => 'Frontend\PaypalController@postPaypal', 'as' => 'credit.paypal'));
    //    Route::get('credit/success', array('uses' => 'Frontend\PaypalController@getSuccess', 'as' => 'credit.success'));
    //    Route::get('credit/cancel', array('uses' => 'Frontend\PaypalController@getCancel', 'as' => 'credit.cancel'));
    //    
    //    #transaction
    //    Route::get('transaction', 'Frontend\TransactionController@index');
    //    Route::any('transaction/search', array('uses' => 'Frontend\TransactionController@search', 'as' => 'transaction.search'));
    //    Route::get('transaction/export', 'Frontend\TransactionController@export');
});


//cron job
Route::get('/cron/bookingstatus', 'Frontend\BookingController@cronBookingStatus');

/** ------------------------------------------
 *  GLOBAL variable define
 *  ------------------------------------------
 */
defined('LOGO_PATH') or define('LOGO_PATH', base_path() . '/uploads/logo/');
defined('LOGO_ROOT') or define('LOGO_ROOT', URL('uploads/logo') . '/');

defined('ADMIN_IMAGE_PATH') or define('ADMIN_IMAGE_PATH', base_path() . '/uploads/admin/');
defined('ADMIN_IMAGE_ROOT') or define('ADMIN_IMAGE_ROOT', URL('uploads/admin') . '/');

defined('USER_IMAGE_PATH') or define('USER_IMAGE_PATH', base_path() . '/uploads/user/');
defined('USER_IMAGE_ROOT') or define('USER_IMAGE_ROOT', URL('uploads/user') . '/');


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');



// set 404 error if any of the route not found
Route::fallback(function () {
    return view('error_message');
});
