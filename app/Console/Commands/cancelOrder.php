<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Booking;
use App\BookingStatusHistory;
use App\Notification;
use App\Transaction;
use App\User;
use Intersoft\Stripe\Http\Services\StripePaymentProcess;

class cancelOrder extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cancel:order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to deleted order exist without accpetance for more than 2 hours';

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
        $twoHourAgo = date('Y-m-d H:i:s', strtotime('-120 minutes'));
        $bookings = Booking::where([['vender_id', '=', NULL], ['created_at', '<', $twoHourAgo], ['status_id', '=', Booking::orderPlaced]])->get();
        foreach ($bookings as $booking) {
            $booking->status_id = Booking::orderCanceled;
            $reason = 'Assist providers canceled the booking due to unavailability of vendors, So kindly do another orders.';
            $booking->reason_of_cancellation = $reason;
            $booking->save();
            $stripe_payment = new StripePaymentProcess(env('STRIPE_SECRET_KEY'));
            $status = $stripe_payment->refund_payment($booking->id);
            $transaction = Transaction::where('booking_id', $booking->id)->first();

            if ($status) {
                $transaction->state = 'Payment refunded to user';
            } else {
                $transaction->state = 'Payment not refunded to user';
            }
            $transaction->save();
            $getVenders = Notification::where('type_id', '=', $booking->id)->get();
            if ($getVenders) {
                foreach ($getVenders as $getVender) {
                    $usr = User::where('id', '=', $getVender->user_id)->first();
                    if ($usr->hasRole('vendor')) {

                        $msg = 'This booking is cancelled due to not accepted by any vender';
                        Notification::createNotification($booking->id, Notification::cancelbooking, Notification::cancelBookingTitle, $msg, $getVender->user_id);
                    }
                }
            }
            /// refund
            $data = array(
                'booking_id' => $booking->id,
                'status_id' => Booking::orderCanceled,
            );
            BookingStatusHistory::create($data);
            $message = 'Your booking with assist has cancelled. Cancelation reason is :- ' . $reason;
            Notification::createNotification($booking->id, Notification::cancelbooking, Notification::cancelBookingTitle, $message, $booking->user_id);
        }
    }
}
