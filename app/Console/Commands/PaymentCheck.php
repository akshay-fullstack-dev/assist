<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\User;
use App\UserPackage;
use Carbon\Carbon;
use Exception;

class PaymentCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will verify & check the payment for all the users and change the user status according to the payment';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            // get the latest payment purchased by user
            $user_payments = UserPackage::select(\DB::raw('*,max(id) as id'))->groupBy('user_id')->orderBy('id', 'ASC')->get();
            // if payments found then procedd to change the user status
            if ($user_payments->count()) {
                if ($access_token = UserPackage::GetGoogleToken()) {
                    foreach ($user_payments as $user_payment) {
                        $user = User::where('id', $user_payment->user_id)->first();
                        if ($user->count() > 0) {
                            // get all the user payments from google and verify
                            $google_response = UserPackage::GetUserPackageDetailsFromGoogle($user_payment->purchase_token, $access_token, $user_payment->product_id);
                            // if google not found any of package
                            if ($google_response == false) {
                                UserPackage::where('id', $user_payment->id)->update(['status' => UserPackage::inActivePackage]);
                                $user->payment_status = UserPackage::inActivePackage;
                            } else {
                                if (($google_response['expiryTimeMillis'] / 1000) <  strtotime(Carbon::now())) {
                                    UserPackage::where('id', $user_payment->id)->update(['status' => UserPackage::inActivePackage]);
                                    $user->payment_status = UserPackage::inActivePackage;
                                } else {
                                    UserPackage::where('id', $user_payment->id)->update(['statu' => UserPackage::activePackage]);
                                    $user->payment_status = UserPackage::activePackage;
                                }
                            }
                            $user->save();
                        }
                    }
                }
            }
        } catch (Exception $e) {
            echo 'someting went wrong';
            die;
        }
    }
}
