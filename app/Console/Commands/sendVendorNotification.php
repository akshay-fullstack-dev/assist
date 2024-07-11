<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Notification;
use App\Booking;
use App\User;
use Illuminate\Support\Facades\DB;
use App\BookedSlot;
use App\slot;
use Carbon\Carbon;

class sendVendorNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will send the booking service notification';

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
        $date = Carbon::now(); //->addMinutes($event->notice)
        $slot_start_from  = Carbon::now()->subMinutes(30);
        $slot_start_within =  Carbon::now()->subMinutes(25);

        $booking_status = Booking::orderPlaced;
        $status_id = array(Booking::venderAssigned, Booking::venderOnTheWay, Booking::orderInProgres, Booking::venderArived);
        $bookings = Booking::whereDate('booking_date', $date)->where('slot_start_from', '>=', $slot_start_from)->where('slot_start_from', '<=', $slot_start_within)->whereStatus_id($booking_status)->whereIs_onhold(Booking::bookingNotInHold)->get();
        if ($bookings->count() > 0) {
            foreach ($bookings as $booking) {
                $user_address = $booking->user->selectedAddress;
                $slot_ids = slot::where([['slot_from', '>=', $booking->slot_start_from], ['slot_to', '<=', $booking->slot_start_end], ['day', '=', date('w', strtotime($booking->booking_date))]])->pluck('id');
                if ($slot_ids->count()) {
                    $vender_in_near_area = $this->getAvailableVendors($user_address->latitude, $user_address->longitude,  "!=", User::proUser, $booking->service_id, $slot_ids, 1);

                    // check vendor for its booked slots if vendor have booked slots then we will not send any request to that vendor
                    $avail_venders = array();
                    if ($vender_in_near_area->count()) {
                        foreach ($vender_in_near_area as $vender) {
                            $flag = 0;
                            $check_user_booking = BookedSlot::where(['vender_id' => $vender->user_id])->where('slot_id', $slot_ids)->whereIn('status_id', $status_id)->whereDate('booking_date', '=', $booking->booking_date)->first();
                            if (!empty($check_user_booking)) {
                                $flag = 1;
                            }
                            if (!$flag) {
                                $avail_venders[] = $vender;
                            }
                        }
                        if ($avail_venders) {
                            foreach ($avail_venders as $avail_vender) {
                                $message = 'Hi ' . $avail_vender->firstname . ' you have a new booking request';
                                Notification::createNotification($booking->id, Notification::booking, Notification::bookingService, $message, $avail_vender, $foradmin = false);
                            }
                        }
                    }
                }
            }
        }
    }


    private  function getAvailableVendors($lat, $long, $condition, $pro_ratings, $sub_service_id, $slot_ids, $id = '')
    {
        $searching_area_in_km = env('SEARCHING_AREA', 20);
        $query = DB::table('user_addresses')
            ->select(DB::raw(" *, count(*) as cnt, ( 6371 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($long) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) ) AS distance "))
            ->havingRaw("distance <= $searching_area_in_km")
            ->join('users', 'user_addresses.user_id', '=', 'users.id')
            ->join('vender_slots', 'users.id', '=', 'vender_slots.vender_id')
            ->join('vender_services', 'users.id', '=', 'vender_services.vender_id')
            ->whereRaw("`users`.`status`= '1'")
            ->whereRaw("`users`.`online` = '1'")
            ->whereRaw("`users`.`isPro` $condition  '$pro_ratings'")
            ->where('vender_services.service_id', '=', $sub_service_id);
        if ($id) {
            $query->whereIn('vender_slots.slot_id', $slot_ids)->groupBy('user_id');
        }
        $vender_in_near_area = $query->get();

        return $vender_in_near_area;
    }
}
