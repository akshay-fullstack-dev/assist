<?php

namespace App\Providers;

use App\Services\PushNotification;
use Illuminate\Support\Facades\Schema; //NEW: Import Schema
use Illuminate\Support\ServiceProvider;
use Braintree_Configuration;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Schema::defaultStringLength(191); //NEW: Increase StringLength
        
        Braintree_Configuration::environment(env('BRAINTREE_ENV'));
        Braintree_Configuration::merchantId(env('BRAINTREE_MERCHANT_ID'));
        Braintree_Configuration::publicKey(env('BRAINTREE_PUBLIC_KEY'));
        Braintree_Configuration::privateKey(env('BRAINTREE_PRIVATE_KEY'));
        //$userStatus = Auth::User();
        
        //view()->share('status', $userStatus->status);
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind('PushNotification', function ($app) {
            return new PushNotification();
        });
    }
}
