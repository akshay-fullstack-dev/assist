<?php

namespace App\Console\Commands;

use App\Booking;
use App\Notification;
use App\User;
use Illuminate\Console\Command;


class sendBookingNotificationToVendor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:bookingNotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send booking reminder for the vendor if his request is after the minutes';

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
        $date = gmdate('Y-m-d H:i:s');
        $slot_start_from_upper_limit = date('H:i:s', strtotime('+55 minutes', strtotime($date)));
        $slot_start_from_lower_limit = date('H:i:s', strtotime('+60 minutes', strtotime($date)));

        $bookings = Booking::whereDate('booking_date', date('Y-m-d', strtotime($date)))->whereTime('slot_start_from', '>=', $slot_start_from_upper_limit)->whereTime('slot_start_from', '<=', $slot_start_from_lower_limit)
            ->whereNotNull('vender_id')
            ->get();
        if ($bookings->count() > 0) {
            foreach ($bookings as $booking) {
                $user = User::find($booking->vender_id);
                $message = trans('api/service.booking_reminder_msg', ['username' => $user->firstname . ' ' . $user->lastname, 'time' => $booking->slot_start_from]);
                Notification::createNotification($booking->id, Notification::booking_reminder, trans('api/service.booking_reminder'), $message, $booking->user_id);
            }
        }
    }
}
