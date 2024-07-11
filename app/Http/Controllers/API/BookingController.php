<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Service;
use App\ServiceCategory;
use App\Equipment;
use App\slot;
use App\BookedSlot;
use App\User;
use App\Review;
use App\BookingStatusHistory;
use App\venderSlot;
use App\UserAddresses;
use App\Transaction;
use App\Booking;
use App\ExtraHour;
use App\BookedEquipment;
use App\CouponHistory;
use App\PaymentSetting;
use App\BookingDetail;
use App\Notification;
use App\userCoupon;
use App\Coupon;
use App\CancelledBooking;
use App\Services\PushNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Booking as BookingResource;
use App\Http\Resources\venderBookingList;
use App\Http\Resources\Avatar;
use App\AvatarImage;
use App\Http\Requests\CheckServiceAvailabilityRequest;
use App\Http\Requests\NotifyMeRequest;
use App\Http\Resources\venderBookingListCollection;
use App\Http\Resources\BookingCollection;
use App\Http\Resources\BookedEquipment as BokedEquip;
use App\Http\Resources\Invoice as InvoiceResource;
use App\Http\Resources\NotificationCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\BookingHistoryCollection;
use App\NotifyMe;
use Braintree\Transaction as BrainTreeTransaction;
use Illuminate\Support\Facades\Log;
use Intersoft\Stripe\Http\Services\StripePaymentProcess;
use Symfony\Component\HttpFoundation\Response;

class BookingController extends Controller
{
    use \App\Traits\PaybaseApiTrait;

    protected $response = [
        'status' => 0,
        'message' => '',
    ];

    const totalRow = 20;
    //const cancelation_time = 86400;
    const cancelation_time = 300;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $service;
    protected $pageLimit;

    public function __construct(Service $service)
    {
        $this->service = $service;
        $this->pageLimit = config('settings.pageLimit');
        $this->response['data'] = new \stdClass();
    }

    //function to book service

    /**
     * @param booking details
     */
    public function bookService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sub_service_id' => ['required'],
            'address_id' => ['required'],
            'selected_date' => ['required'],
            'price' => ['required'],
            'payment_type' => ['required'],
            'nonce_token' => 'required_if:payment_type,==,0',
            'price_type' => 'required_if:vender_id,==,0',   // for pro and standard price
            'payment.card_id' => 'required|string',
            'payment.currency_code' => 'required|string'
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }

        $booking_date = gmdate('Y-m-d 00:00:00', strtotime($request['selected_date']));
        if ($request['slot_id'] == '0') {
            $booking_date = gmdate('Y-m-d 00:00:00');
        }

        $user = Auth::User();
        if ($user->stripe_id == '0') {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/user.not_connected_payment_gateway');
            return response()->json($this->response, 403);
        }
        $slot = '';
        $address = '';
        // check if scheduled booking or book now
        if (!empty($request['slot_ids'])) {

            $slot = slot::whereIn('id', $request['slot_ids'])->orderBY('slot_from', 'ASC')->get();
        } else {

            $day = gmdate('N');
            $time = gmdate("H:i:s");
            $plusOneHour = gmdate('H:i', strtotime($time . '+1 hour'));

            $slot = slot::where([['slot_from', '>=', $time], ['slot_from', '<=', $plusOneHour], ['day', '=', $day]])->first();
            if (!$slot) {
                $this->response['status'] = 0;
                $this->response['message'] = trans('api/user.no_vender_available_you_can_book_please_try_after_some_time');
                return response()->json($this->response, 403);
            }
            $checkSlotAssigned = venderSlot::where([['slot_id', '=', $slot->id]])->first();
            if (!$checkSlotAssigned) {
                $this->response['status'] = 0;
                $this->response['message'] = trans('api/user.no_vender_available_you_can_book_please_try_after_some_time');
                return response()->json($this->response, 403);
            }
        }
        //get adress of user
        if ($request['address_id']) {
            $address = UserAddresses::where(['id' => $request['address_id']])->first();
        }
        $all_slots_time = '';
        $slotStart = '';
        $slotEnd = '';
        $slotId = '';
        if (!empty($request['slot_ids'])) {
            $numberOfSlots = count($slot);
            $i = 1;
            foreach ($slot as $s) {
                if ($all_slots_time != '')
                    $all_slots_time .= ', ';
                if ($i == 1)
                    $slotStart = $s->slot_from;
                if ($i == $numberOfSlots)
                    $slotEnd = $s->slot_to;
                $all_slots_time .= gmdate('H:i:s', strtotime($s->slot_from)) . " - " . gmdate('H:i:s', strtotime($s->slot_to));
                $i++;
            }
        } else {
            $slotStart = $slot->slot_from;
            $slotEnd = $slot->slot_to;
            $slotId = $slot->id;
            $all_slots_time = gmdate('H:i:s', strtotime($slot->slot_from)) . " - " . gmdate('H:i:s', strtotime($slot->slot_to));
        }
        //if promocode is applied
        if ($request['promo_code']) {
        }
        //check what is payment type online/cod
        if ($request['payment_type']) {
        }
        //get selected service details to save with booking
        $service_name = Service::where(['id' => $request['sub_service_id']])->first();
        $data['user_id'] = $user->id;
        $data['service_id'] = $request['sub_service_id'];
        $data['full_name'] = isset($address->name) ? $address->name : $user->firstname . ' ' . $user->lastname;
        $data['email'] = $request['email'] ? $request['email'] : $user->email;
        $data['phone'] = isset($address->phone) ? $address->phone : $user->phone_number;
        $data['price'] = $request['hourly_price'] ? $request['hourly_price'] : $request['price'];
        $data['booking_date'] = $booking_date;
        $data['total_price'] = $request['price'];
        $data['slot_start_from'] = $slotStart;
        $data['slot_start_end'] = $slotEnd;
        $data['slots_time'] = $all_slots_time;
        if (!empty($request['slot_ids'])) {
            $data['slot_id'] = 0;
        } else {
            $data['slot_id'] = $slotId;
        }
        $data['status_id'] = Booking::orderPlaced;
        //if ($request['price_type'] == '1') {
        if (!empty($request['slot_ids'])) {
            $data['selected_hours'] = count($request['slot_ids']);
        } else {
            $data['selected_hours'] = 1;
        }
        //}
        $data['booking_note'] = $request['booking_note'] ? $request['booking_note'] : '';
        $data['notes'] = $request['notes'] ? $request['notes'] : '';
        $data['job_description'] = $request['job_description'] ? $request['job_description'] : '';
        $data['additional_equipments'] = $request['additional_equipments'] ? $request['additional_equipments'] : '';
        $data['address'] = isset($address->full_address) ? $address->full_address : '';
        $data['booking_type'] = $request['booking_type'] ? $request['booking_type'] : '';
        $data['payment_type'] = $request['payment_type'] ? $request['payment_type'] : '';
        $data['service_name'] = isset($service_name->title) ? $service_name->title : "";
        $booking = '';

        $lat = $address->latitude;
        $long = $address->longitude;
        // Query to get nearest online venders who provide service selected by user for booking
        $vender_in_near_area = '';

        // get user is pro or standard 
        $price_type = $request['price_type'];
        $pro_ratings = User::proUser;
        if ($price_type == 1) {
            $condition = "=";
        } else {
            $condition = "!=";
        }
        $sub_service_id = $request['sub_service_id'];
        // if we have vendor id in request then find the vendor according to the vendor_id
        if (isset($request['vendor_id']) && $request['vendor_id'] != '') {
            // $vender_in_near_area = User::select('id as user_id')->where('id', $request['vendor_id'])->get();\
            $slot_ids = $request['slot_ids'];
            $vender_in_near_area = $this->getAvailableVendors($lat, $long, $condition, $pro_ratings, $sub_service_id, $slot_ids, 1, $request['vendor_id']);
        } else {
            if (!empty($request['slot_ids'])) {

                $slot_ids = $request['slot_ids'];
                $vender_in_near_area = $this->getAvailableVendors($lat, $long, $condition, $pro_ratings, $sub_service_id, $slot_ids, 1);
            } else {

                $vender_in_near_area = $this->getAvailableVendors($lat, $long, $condition, $pro_ratings,  $sub_service_id, $slot->id);
            }
        }
        //check if any vender found in nearest area
        if (!$vender_in_near_area->count()) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/user.no_vender_found_for_this_service');
            return response()->json($this->response, 403);
        }
        $avail_venders = array();
        //check if vender found then is he booked in selected time slot or not
        foreach ($vender_in_near_area as $vender) {

            $flag = 0;
            $check_user_booking = array();
            $status_id = array(Booking::venderAssigned, Booking::venderOnTheWay, Booking::orderInProgres, Booking::venderArived);
            if (!empty($request['slot_ids'])) {
                foreach ($request['slot_ids'] as $slot_id) {
                    $check_user_booking = BookedSlot::where(['vender_id' => $vender->user_id, 'slot_id' => $slot_id])->whereIn('status_id', $status_id)->whereDate('booking_date', '=', $booking_date)->first();
                    if (!empty($check_user_booking)) {

                        $flag = 1;
                    }
                }
            } else {
                $check_user_booking = BookedSlot::where(['vender_id' => $vender->user_id, 'slot_id' => $slotId])->whereIn('status_id', $status_id)->whereDate('booking_date', '=', $booking_date)->first();
                if (!empty($check_user_booking)) {

                    $flag = 1;
                }
            }
            if (!$flag) {
                $avail_venders[] = $vender->user_id;
            }
        }

        //if no vender is free in current slot then return with message 
        if (empty($avail_venders)) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/user.this_slot_is_booked_for_all_vendeors');
            return response()->json($this->response, 403);
        }

        if (isset($request['vender_id']) && $request['vender_id'] != '' && $request['vender_id'] != 0) {
            if (!in_array($request['vender_id'], $avail_venders)) {
                $this->response['status'] = 0;
                $this->response['message'] = trans('api/user.this_slot_is_booked_for_all_vendeors');
                return response()->json($this->response, 403);
            }
        }

        //if any vender found then send him notification
        if ($avail_venders) {
            $booking = Booking::create($data);
            $transaction = new Transaction();

            if (!empty($request['payment']) && $request['payment_type'] == 2) {
                if ($booking) {
                    $payment_data = $request['payment'];
                    $stripe_secret_key = env('STRIPE_SECRET_KEY');
                    $payment = new StripePaymentProcess($stripe_secret_key);
                    $payment = $payment->IntentPayment($request['price'], $payment_data['card_id'], $payment_data['currency_code'], $booking->id, $user->stripe_id);
                }
            }

            $transaction->trans_id = rand(123456, 987456);
            $admin_comission_percent = PaymentSetting::where('commission', '!=', '')->first();
            if (!$admin_comission_percent) {
                $this->response['message'] = trans('api/service.admin_commission_not_set');
                return response()->json($this->response, 200);
            }
            $transaction->user_id = $booking->user_id;
            $transaction->booking_id = $booking->id;
            $transaction->payment_method = $request['payment_type'];
            $transaction->amount = $request['price'];
            $admin_amount = '';
            $vender_amount = '';
            if ($admin_comission_percent->commission) {
                $admin_amount = ($admin_comission_percent->commission / 100) * $request['price'];
                $vender_amount = $request['price'] - $admin_amount;
            }
            if ($request['payment_type'] == 2) {
                $totalAmountPaid = $request['price'];
                $adminFeePercentage =  $admin_amount;
                $transaction->admin_amount = $adminAmount = ($adminFeePercentage) ? ($totalAmountPaid / 100) * $adminFeePercentage : 0;
                $transaction->vender_amount = $totalAmountPaid - $adminAmount;
            } else {
                $transaction->vender_amount = $vender_amount;
                $transaction->admin_amount = $admin_amount;
            }
            $transaction->currency = $admin_comission_percent->currency_id;
            $transaction->status = 'Paid To Admin';
            $transaction->save();


            //set booking for the requested vendor according to the vendor id
            if (isset($request['vender_id']) && $request['vender_id'] != '' && $request['vender_id'] != 0) {
                $vend_data = User::where('id', '=', $request['vendor_id'])->first();
                $message = 'Hi ' . $vend_data->firstname . ' you have a new booking request';
                $foradmin = false;
                Notification::createNotification($booking->id, Notification::booking, Notification::bookingService, $message, $request['vender_id'], $foradmin);
            } else {
                // send booking notification to the pro user only
                $isProVendorFound = false;
                foreach ($avail_venders as $avail_vender) {
                    $vend_data = User::where('id', '=', $avail_vender)->first();
                    if ($vend_data->payment_status == User::isPaid) {
                        $message = 'Hi ' . $vend_data->firstname . ' you have a new booking request';
                        $foradmin = false;
                        Notification::createNotification($booking->id, Notification::booking, Notification::bookingService, $message, $avail_vender, $foradmin);
                        $isProVendorFound = true;
                    }
                }
                // if there is no pro vendor found then send notification to standard vendor

                if ($isProVendorFound == false) {
                    foreach ($avail_venders as $avail_vender) {
                        if ($vend_data->payment_status != User::isPaid) {
                            $message = 'Hi ' . $vend_data->firstname . ' you have a new booking request';
                            $foradmin = false;
                            Notification::createNotification($booking->id, Notification::booking, Notification::bookingService, $message, $avail_vender, $foradmin);
                        }
                    }
                }
            }

            if ($booking) {
                if (!empty($request['slot_ids'])) {
                    foreach ($request['slot_ids'] as $slot) {
                        $this->addBookedSlot($booking, $slot);
                    }
                } else {
                    $this->addBookedSlot($booking, $slotId);
                }
            }
        }

        //save booking details in booking detail table
        if ($booking) {
            $this->saveBookingDetail($booking, $request['promo_code'], $slotStart, $slotEnd);
        }
        $bookingHistoryData = array(
            'booking_id' => $booking->id,
            'status_id' => Booking::orderPlaced,
            'user_type' => 'user'
        );
        if (isset($request['new_additional_equipments']) && !empty($request['new_additional_equipments'])) {
            $equipments = $request['new_additional_equipments'];
            if ($equipments) {
                $this->additionalEquipments($booking, $equipments);
            }
        }
        if ($user->reffer_code) {
            $this->createCouponForReferer($user, $booking->id);
        }
        if (isset($request['coupon_history_id']) && $request['coupon_history_id'] != '') {
            $couponHistory = CouponHistory::find($request['coupon_history_id']);
            if ($couponHistory) {
                $couponHistory->booking_id = $booking->id;
                $couponHistory->discount = $request['discountAmount'];
                $couponHistory->save();

                if ($request['is_global'] == 1) {
                    $getGlobalCoupon = Coupon::find($couponHistory->coupon_id);
                    $getGlobalCoupon->save();
                } else {
                    $userCoupon = userCoupon::find($couponHistory->coupon_id);
                    $userCoupon->status = '1';
                    $userCoupon->save();
                }
            }
        }

        //create booking history
        BookingStatusHistory::create($bookingHistoryData);
        $this->response['status'] = 1;
        $this->response['message'] = trans('api/service.booked_successfully');
        return response()->json($this->response, 200);
    }

    /**
     * 
     * @param $userId id of user who logged in
     */
    private function createCouponForReferer($user, $bokingid)
    {
        $booking = Booking::where('user_id', $user->id)->get();
        $refererUser = User::where('refferal', $user->reffer_code)->first();
        if ($refererUser && $booking->count() <= 1) {
            $coupon = $this->generate_random_string();
            $coupon2 = $this->generate_random_string();
            $inserted_id = userCoupon::create([
                'user_id' => $refererUser->id,
                'name' => 'Reward On refer Friend',
                'code' => $coupon,
                'type' => 'Fixed',
                'discount' => '5',
                'minAmount' => '',
                'maxTotalUse' => 1,
                'totalUsed' => 1,
                'status' => '0',
                'startDateTime' => date('Y-m-d H:i:s'),
                'endDateTime' => date('d-m-Y H:i:s', strtotime(date('Y-m-d H:i:s') . ' +30 days'))
            ]);


            $inserted_id2 = userCoupon::create([
                'user_id' => $user->id,
                'name' => 'Receive reward on first booking. Because user reffered by a friend',
                'code' => $coupon2,
                'type' => 'Fixed',
                'booking_id' => $bokingid,
                'discount' => '5',
                'minAmount' => '',
                'maxTotalUse' => 1,
                'totalUsed' => 1,
                'status' => '1',
                'startDateTime' => date('Y-m-d H:i:s'),
                'endDateTime' => date('d-m-Y H:i:s')
            ]);
            /* if($inserted_id->id) {
              Mail::to($refererUser->email)->send(new Activate($user));
              } */
        }
    }

    /**
     * 
     * @param type $lat
     * @param type $long
     * @param type $condition
     * @param type $pro_ratings
     * @param type $sub_service_id
     * @param type $slot_ids
     * @param type $id
     * @return type
     */
    private function getAvailableVendors($lat, $long, $condition, $pro_ratings, $sub_service_id, $slot_ids, $id = '', $userid = '')
    {
        $searching_area_in_km = env('SEARCHING_AREA', 20);
        $query = DB::table('user_addresses')
            ->select(DB::raw(" *, count(*) as cnt, ( 6371 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($long) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) ) AS distance "))
            ->havingRaw("distance <= $searching_area_in_km")
            ->join('users', 'user_addresses.user_id', '=', 'users.id')
            ->join('vender_slots', 'users.id', '=', 'vender_slots.vender_id')
            ->join('vender_services', 'users.id', '=', 'vender_services.vender_id')
            ->whereRaw("`users`.`stripe_id` != '0'")
            ->whereRaw("`users`.`status`= '1'")
            ->whereRaw("`users`.`online` = '1'")
            ->whereRaw("`users`.`isPro` $condition  '$pro_ratings'")
            ->where('vender_services.service_id', '=', $sub_service_id);
        if ($userid) {
            $query->where("users.id", '=', $userid);
        }
        if ($id) {
            $query->whereIn('vender_slots.slot_id', $slot_ids)->groupBy('user_id');
        }
        $vender_in_near_area = $query->get();
        return $vender_in_near_area;
    }

    private function addBookedSlot($booking, $slot)
    {
        $BookingData = array(
            'booking_id' => $booking->id,
            'booking_date' => $booking->booking_date,
            'vender_id' => 0,
            'status_id' => Booking::orderPlaced,
            'slot_id' => $slot
        );
        BookedSlot::create($BookingData);
    }

    /**
     * 
     * @param type $booking
     * @param type $promoCode
     * @param type $slotStart
     * @param type $slotEnd
     */
    private function saveBookingDetail($booking, $promoCode, $slotStart, $slotEnd)
    {
        BookingDetail::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_price,
            'code' => $promoCode ? $promoCode : '',
            'start_time' => $slotStart,
            'end_time' => $slotEnd
        ]);
    }

    /**
     * 
     * @param type $booking
     * @param type $equipments
     */
    private function additionalEquipments($booking, $equipments)
    {
        foreach ($equipments as $equipment) {
            $equ = Equipment::where('id', $equipment['id'])->first();
            BookedEquipment::create([
                'booking_id' => $booking->id,
                'equipment_id' => $equ->id,
                'equipment_name' => $equ->name,
                'price' => ($equ->price * $equipment['qty']),
                'quantity' => $equipment['qty']
            ]);
        }
    }

    /**
     * 
     * @param Request $request
     * @return type
     * @function to accept scheduled and rescheduled booking 
     * @parameter : booking_id, status
     * @Booking_id :- id of booking
     * @Status :- 0 : , 1 :- , 2 :- 
     * ************************ */
    public function acceptBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => ['required'],
            'status' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $user = Auth::User();
        $is_rescheduling = 0;
        $booking = Booking::where('id', '=', $request['booking_id'])->first();

        // if booking not found then return the meesage to the vendor that booking not found
        if (!$booking) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.service_not_found');
            return response()->json($this->response, 404);
        }

        if ($booking->is_onhold == '1') {
            $this->response['message'] = trans('api/service.booking_on_hold');
            return response()->json($this->response, 403);
        }

        if ($request['reject_reschedule'] == 1) {
            // if vendor booking on hold then then it can't be extends request  
            // if booking status will be on hold then request terminated here with proper message


            $booking->status_id = Booking::orderCanceled;
            $booking->save();
            $username = isset($booking->user->firstname) ? $booking->user->firstname : " User";
            $vendor = isset($booking->vender->firstname) ? $booking->vender->firstname : "vendor";
            BookedSlot::where('booking_id', '=', $request['booking_id'])->update(array('status_id' => Booking::orderCanceled));
            $message = "Hi " . $username . ", Your reschedule request has been rejected by " . $vendor . ". So, your booking got cancelled.";
            $not = Notification::createNotification($booking->id, Notification::cancelbooking, Notification::cancelBookingTitle, $message, $booking->user_id);
            $not->rejected_booking = 1;
            $not->save();
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/service.thanks_for_react');
            return response()->json($this->response, 200);
        }

        //check if user accept or reject booking status = 0 rejected, status 1 = accepted
        if ($request['status'] == 0) {

            $notification = Notification::where(['user_id' => $user->id, 'type_id' => $request['booking_id'], 'title' => Notification::bookingService])->first();
            if ($notification) {
                $notification->rejected_booking = 1;
                $notification->save();
            }
            if ($booking->vender_id == $user->id) {
                $bookingHistory = new BookingStatusHistory();
                $bookingHistory->booking_id = $booking->id;
                $bookingHistory->status_id = Booking::orderCanceled;
                $bookingHistory->user_type = 'vendor';
                $bookingHistory->save();
                $booking->status_id = Booking::orderCanceled;
                $booking->save();
            }
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/service.thanks_for_react');
            return response()->json($this->response, 200);
        }
        // get booking which vender want to accept
        //check availability of vender if he is free for this slot
        $check_avail = venderSlot::where(['slot_id' => $booking->slot_id, 'vender_id' => $user->id])->get();
        //if vender not working in this slot then set him message 
        if (!$check_avail) {
            $this->response['message'] = trans('api/service.you_cant_work_in_this_slot');
            return response()->json($this->response, 404);
        }

        $status_id = array(Booking::venderAssigned, Booking::venderOnTheWay, Booking::orderInProgres, Booking::venderArived);
        $flag = 0;
        if ($request['slot_ids']) {
            $slot = slot::whereIn('id', $request['slot_ids'])->orderBY('slot_from', 'ASC')->get();
            foreach ($request['slot_ids'] as $slot_id) {
                $check_user_booking = BookedSlot::where([['vender_id', '=', $booking->vender_id], ['slot_id', '=', $slot_id],])
                    ->whereIn('status_id', $status_id)
                    ->whereDate('booking_date', '=', $booking->booking_date)
                    ->first();
            }

            if ($check_user_booking) {
                $flag = 1;
            }
        }
        // Booking time passed validation
        if (strtotime(date('Y-m-d', strtotime($booking->booking_date)) . " " . $booking->slot_start_from) < strtotime(date('Y-m-d H:i:s'))) {
            $this->response['message'] = trans('api/booking.booking_date_already_passed');
            return response()->json($this->response, 403);
        }

        // check if user is free for current slot if not then ask user to book another slot
        if ($flag) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.you_are_not_free_in_this_slot');
            return response()->json($this->response, 403);
        }
        //check if booking is accepted by any other user

        if ($booking->status_id != Booking::orderPlaced) {
            $this->response['message'] = trans('api/service.already_booked');
            return response()->json($this->response, 404);
        }
        //check if vender has pending payment
        if ($user->pending_payment > 99) {
            $this->response['message'] = trans('api/service.commission_pending');
            return response()->json($this->response, 403);
        }



        if (isset($booking->vender_id)) {
            $is_rescheduling = 1;
        }
        $accepted_at = date('Y-m-d H:i:s');
        $data = array(
            'vender_id' => $user->id,
            'vender_name' => $user->firstname . ' ' . $user->lastname,
            'status_id' => Booking::venderAssigned,
            'accepted_at' => $accepted_at,
        );
        //update booking status and assign booking to vender
        if ($booking->update($data)) {



            //BookedSlot::where('booking_id', '=', $request->booking_id);
            BookedSlot::where('booking_id', '=', $booking->id)->update(array('status_id' => Booking::venderAssigned, 'vender_id' => $user->id));
            $bookingHistory = new BookingStatusHistory();
            $bookingHistory->booking_id = $booking->id;
            $bookingHistory->status_id = Booking::venderAssigned;
            $bookingHistory->user_type = 'vendor';
            $bookingHistory->save();
            // create booking history while accept order
            $userName = $booking->user->firstname . ' ' . $booking->user->lastname;
            $venderName = $booking->vender->firstname . ' ' . $booking->vender->lastname;
            $message = 'Hi ' . $userName . ', Your job has been accepted by ' . $venderName . '. He will arrive at your place on time.';
            //send notification to user that some vender accpeted his/her booking request
            Notification::createNotification($booking->id, Notification::acceptBooking, Notification::acceptBookingTitle, $message, $booking->user_id);
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/service.accepted_successfully');
            return response()->json($this->response, 200);
        }
    }

    /*     * ****************************
      function to update various status of bookiing like vender is on the way, vender arrived on location, work in progress etc.
     * Parameter order_id, Status 
     * Order id :- booking id
     * status :- 3 :- vendor on the way, 4 :- job in progress, 5 :- job completed, 11 :- vender arrived,
     */

    public function updateOrderStatus(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'order_id' => ['required'],
            'status' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }

        $booking = Booking::findOrFail($request['order_id']);
        // if vendor booking on hold then then it can't be extends request  
        // if booking status will be on hold then request terminated here with proper message
        if ($booking->is_onhold == '1') {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.booking_on_hold');
            return response()->json($this->response, 403);
        }

        $booking->status_id = $request['status'];
        $booking->save();
        BookedSlot::where('booking_id', '=', $booking->id)->update(array('status_id' => $request['status']));
        $user_type = 'user';
        $user = Auth::User();
        if ($user->hasRole('vendor')) {
            $user_type = 'vendor';
        }
        $data = array(
            'booking_id' => $request->order_id,
            'status_id' => $request->status,
            'user_type' => $user_type
        );
        BookingStatusHistory::create($data);
        $message = 'Hi ' . $booking->user->firstname . ' Your job status has been updated by ' . $booking->vender->firstname . '.';
        $bookingTitle = '';
        if ($request['status'] == Booking::venderAssigned) {
            $bookingTitle = Notification::acceptBookingTitle;
        }
        if ($request['status'] == Booking::venderOnTheWay) {
            $bookingTitle = Notification::venderOnTheWayBooking;
        }
        if ($request['status'] == Booking::orderInProgres) {
            $bookingTitle = Notification::venderStartedJob;
        }
        if ($request['status'] == Booking::orderCompleted) {
            $bookingTitle = Notification::orderCompleted;
            $message = trans(
                'api/service.job_completed_message',
                [
                    'username' => $booking->user->firstname,
                    'vendor_name' => $booking->vender->firstname
                ]
            );
            if ($booking->payment_type == 1) {
                $prev_amount = $user->pending_payment;
                $admin_comission_percent = PaymentSetting::where('commission', '!=', '')->first();
                $this_order_commition = 0;
                $this_order_commition = $booking->total_price / 10;
                if (isset($admin_comission_percent->commission)) {
                    $this_order_commition = ($admin_comission_percent->commission / 100) * $booking->total_price;
                }
                $user->pending_payment = $prev_amount + $this_order_commition;
                $user->save();
            } elseif ($booking->payment_type == 2) {
                try {
                    $stripe_payment = new StripePaymentProcess(env('STRIPE_SECRET_KEY'));
                    $transaction = Transaction::where('booking_id', $booking->id)->with('crncy')->first();
                    $status = $stripe_payment->transferCharges($transaction, $booking);
                    if ($status) {
                        $transaction->state = 'Paid to vendor';
                    } else {
                        $transaction->state = 'Payment not transfer to the vendor';
                    }
                    $transaction->save();
                } catch (\Exception $ex) {
                    $this->response['message'] = $ex->getMessage();
                    return response()->json($this->response, 422);
                }
            }
        }
        Notification::createNotification($booking->id, Notification::booking, $bookingTitle, $message, $booking->user_id);
        $data = '';
        $status_id = array(Booking::orderPlaced, Booking::venderAssigned, Booking::venderOnTheWay, Booking::orderInProgres, Booking::venderArived, Booking::orderCompleted);
        $booking_history = BookingStatusHistory::where('booking_id', '=', $booking->id)->whereIn('status_id', $status_id)->groupBy('status_id')->orderBy('id', 'desc')->get();
        return (new BookingHistoryCollection($booking_history))->additional([
            'status' => 1,
            'message' => trans('api/service.order_status_changed')
        ]);
    }

    /*
     * Function get Detail of Invoice
     * Parameter :- Booking Id
     */

    public function getInvoiceDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required'
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $booking = Booking::findOrFail($request['booking_id']);
        return (new InvoiceResource($booking))->additional([
            'status' => 1,
            'message' => trans('api/service.booking_detail_found')
        ]);
    }

    /*
     * Function get details of Order
     * Parameter :- category_ids
     * 
     */

    public function getOrders(Request $request)
    {

        $status = '';
        $service_ids = array();
        $status_id = array(Booking::orderPlaced, Booking::venderAssigned, Booking::venderOnTheWay, Booking::orderInProgres, Booking::venderArived);
        if (isset($request['status']) && !empty($request['status'])) {
            $status_id = $request['status'];
        }

        if (isset($request['category_ids']) && !empty($request['category_ids'])) {

            $category_ids = $request['category_ids'];
            $service_ids = Service::select('id')->whereIn('cat_id', $category_ids)->get()->toArray();

            $service_ids = array_column($service_ids, 'id');
        }
        $user = Auth::User();
        $query = Booking::where(['user_id' => $user->id])
            ->whereIn('status_id', $status_id);
        if (!empty($service_ids)) {
            $query->whereIn('service_id', $service_ids);
        }

        $all_orders = $query->orderBy('id', 'DESC')->paginate(self::totalRow);
        return (new OrderCollection($all_orders))->additional([
            'status' => 1,
            'message' => trans('api/user.all_orders')
        ]);
    }

    //function to get booking detail
    public function getBookingDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $message = '';
        $user = Auth::User();
        $booking = Booking::where(['id' => $request['booking_id']])->first();

        if (!$booking) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.booking_not_exist');
            $this->response['data'] = array();
            return response($this->response, 404);
        }

        // if vendor booking on hold then then it can't be extends request  
        // if booking status will be on hold then request terminated here with proper message
        if ($booking->is_onhold == '1') {

            $this->response['message'] = trans('api/service.booking_on_hold');
            return response()->json($this->response, 403);
        }

        if ($user->hasRole(['vendor'])) {
            $message = trans('api/service.vender_accepted_booking_detail');
        } else {
            $message = trans('api/service.booking_detail');
        }
        return (new BookingResource($booking))->additional([
            'status' => 1,
            'message' => $message
        ]);
    }

    // function to cancel booking
    public function cancelBooking(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'booking_id' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }


        $user = Auth::User();
        $chek_role = '';
        //check vender role
        if ($user->hasRole('vendor')) {
            $check_role = 'vender';
        } else {
            $check_role = 'user';
        }

        $booking_id = $request['booking_id'];
        $booking = Booking::where(['id' => $booking_id])->first();

        // if vendor booking on hold then then it can't be extends request  
        // if booking status will be on hold then request terminated here with proper message
        if ($booking->is_onhold == '1') {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.booking_on_hold');
            return response()->json($this->response, 403);
        }

        $booking_start_date_time = strtotime(date('Y-m-d', strtotime($booking->booking_date)) . ' ' . $booking->slot_start_from);
        $today = strtotime(date('Y-m-d H:i'));
        // return if order is already completed
        if ($booking->status_id == Booking::orderCompleted) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.job_already_completed');
            return response()->json($this->response, 403);
        }
        //check if vender is on the ay or order in progress
        if ($booking->status_id == Booking::venderOnTheWay || $booking->status_id == Booking::orderInProgres || $booking->status_id == Booking::venderArived) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.vender_on_the_way');
            return response()->json($this->response, 403);
        }
        //check if booking time is passed
        if ($booking_start_date_time < $today) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.booking_time_passed_out');
            return response()->json($this->response, 403);
        }

        $left_time_in_booking = $booking_start_date_time - $today;
        $vender_accepted_at = $booking->accepted_at;
        $user_created_at = $booking->created_at;
        // if vender try to cancel booking 
        //$cancelation_time = 86400;
        $cancelation_time = self::cancelation_time;
        if ($check_role == 'vender') {

            $accepted_time = strtotime($vender_accepted_at);
            $can_cancel = $today - $accepted_time;
            // check if booking time passed more than 24 hours

            if ($can_cancel > $cancelation_time) {

                $this->response['status'] = 0;
                $this->response['message'] = trans('api/service.cant_cancel_24_hours_passed_in_booking_accept');
                return response()->json($this->response, 403);
            }
        }

        if ($check_role == 'user') {
            $accepted_time = strtotime($user_created_at);
            $user_can_cancel = $today - $accepted_time;
            // check if booking time passed more than 24 hours
            if ($user_can_cancel > $cancelation_time) {

                $this->response['status'] = 0;
                $this->response['message'] = trans('api/service.cant_cancel_24_hours_passed_in_booking_create');
                return response()->json($this->response, 403);
            }
        }

        $transaction = Transaction::where('booking_id', $booking->id)->first();
        if ($transaction->payment_type == 2) {
            try {
                $body['toStateId'] = "ADJUSTED";
                $txns = $this->paybaseApi($body, "https://api-json.sandbox.paybase.io/v1/tx/" . $transaction->trans_id . "/state", "patch");
                $transaction->state = $txns->stateId;
                $transaction->save();
            } catch (\Exception $ex) {
                $this->response['message'] = $this->paybaseExceptionErrorMessage($ex, "Transaction failed");
                return response()->json($this->response, $ex->getCode());
            }
        }

        $booking->status_id = Booking::orderCanceled;
        $booking->cancelled_by = $user->id;
        if (isset($request['reason_of_cancellation'])) {

            $booking->reason_of_cancellation = $request['reason_of_cancellation'];
        }


        if ($check_role != 'vender') {
            if ($booking->vender_id == '' || $booking->vender_id == NULL) {
                //echo 'full refund';
            }
            // check if booking cancelled by vender after some time
        }

        //        if($check_role == 'vender'){
        //            $booking_accepted = strtotime(date('Y-m-d H:i:s', strtotime($booking->accepted_at))); 
        //            //$cur_time = strtotime(date('2019-06-20 17:59:00')); 
        //            
        //            echo date('Y-m-d H:i:s');
        //            echo '<br>'.$booking->accepted_at; exit;
        //            $cur_time = strtotime(date('Y-m-d H:i:s'));
        //            $diff = $cur_time - $booking_accepted;
        //            echo $diff; exit;
        //            echo ($diff / 60); exit;
        //            if (($diff / 60) <= 120) {
        //                
        //                $data = array(
        //                    'vender_id' => $booking->vender_id,
        //                    'booking_id' => $booking->id
        //                );
        //                CancelledBooking::create($data);
        //            }
        //        }

        $booking->save();
        BookedSlot::where('booking_id', '=', $booking->id)->update(array('status_id' => Booking::orderCanceled));
        $booking_created_date = strtotime(date('Y-m-d', strtotime($booking->created_at)));
        $current_time = strtotime(date('Y-m-d H:i:s'));
        $difference = $current_time - $booking_created_date;
        //check for refund. if booking did just before 10 min then decide about refund amount
        if ($difference / 60 <= 10) {

            //echo "refund all money";
        }

        if ($check_role) {

            $message = $user->firstname . ' has cancelled your booking, because ' . $request['reason_of_cancellation'];
            $send_notification_to = '';
            $user = '';
            if ($check_role == 'vender') {
                $send_notification_to = $booking->user_id;
                $user = 'vender';
            } else {
                if (isset($booking->vender_id) && $booking->vender_id != '' && $booking->vender_id != NULL) {
                    $send_notification_to = $booking->vender_id;
                    $user = 'user';
                }
            }
            $data = array(
                'booking_id' => $booking->id,
                'status_id' => Booking::orderCanceled,
                'user_type' => $user
            );
            BookingStatusHistory::create($data);
            Notification::createNotification($booking->id, Notification::cancelbooking, Notification::cancelBookingTitle, $message, $send_notification_to);
        }

        $this->response['status'] = 1;
        $this->response['message'] = trans('api/service.booking_cancel_successfully');
        return response()->json($this->response, 200);
    }

    // function to reschedule booking
    public function rescheduleBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => ['required'],
            'selected_date' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }


        $user = Auth::User();
        $booking_id = $request['booking_id'];
        $booking = Booking::where(['id' => $booking_id])->first();

        // if vendor booking on hold then then it can't be extends request  
        // if booking status will be on hold then request terminated here with proper message
        if ($booking->is_onhold == '1') {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.booking_on_hold');
            return response()->json($this->response, 403);
        }

        //check if order is accepted by vender or not if not then  rescheduling is not possible
        if ($booking->status_id != Booking::venderAssigned) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.can_not_reschedule_booking');
            return response()->json($this->response, 403);
        }
        $booking_date = date('Y-m-d 00:00:00', strtotime($request['selected_date']));
        $slotStart = '';
        $slotEnd = '';
        $flag = 0;
        if ($request['slot_ids']) {
            $slot = slot::whereIn('id', $request['slot_ids'])->orderBY('slot_from', 'ASC')->get();
            foreach ($request['slot_ids'] as $slot_id) {
                $check_user_booking = Booking::where([
                    ['vender_id', '=', $booking->vender_id],
                    ['slot_id', '=', $slot_id],
                    ['status_id', '>=', Booking::venderAssigned],
                    ['status_id', '<', Booking::orderCompleted],
                ])
                    ->whereDate('booking_date', '=', $booking_date)
                    ->first();
            }

            if ($check_user_booking) {
                $flag = 1;
            }
            $all_slots_time = '';
            $numberOfSlots = count($slot);
            $i = 1;
            foreach ($slot as $s) {
                if ($all_slots_time != '')
                    $all_slots_time .= ', ';
                if ($i == 1) {
                    $slotStart = $s->slot_from;
                }
                if ($i == $numberOfSlots) {
                    $slotEnd = $s->slot_to;
                }
                $all_slots_time .= date('H:i:s', strtotime($s->slot_from)) . " - " . date('H:i:s', strtotime($s->slot_to));
                $i++;
            }
        }
        // check if user is free for current slot if not then ask user to book another slot
        if ($flag) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.choose_another_slot');
            return response()->json($this->response, 403);
        }

        $slot_id = $request['slot_id'];
        $slot = slot::where(['id' => $slot_id])->first();
        $booking->booking_date = date('Y-m-d', strtotime($request['selected_date']));
        $booking->slot_start_from = $slotStart;
        $booking->slot_start_end = $slotEnd;
        $booking->selected_hours = count($request['slot_ids']);
        $booking->slot_id = 0;
        $booking->slots_time = $all_slots_time;
        $booking->status_id = Booking::orderPlaced;
        $send_notification_to = $booking->vender_id;
        //save booking
        if ($booking->save()) {
            BookedSlot::where('booking_id', $booking->id)->delete();
            if ($request['slot_ids']) {
                foreach ($request['slot_ids'] as $slt) {

                    $BookingData = array(
                        'booking_id' => $booking->id,
                        'booking_date' => $booking->booking_date,
                        'vender_id' => $booking->vender_id,
                        'status_id' => $booking->status_id,
                        'slot_id' => $slt
                    );
                    BookedSlot::create($BookingData);
                }
            }

            $data = array(
                'booking_id' => $booking->id,
                'status_id' => Booking::rescheduled,
                'user_type' => 'user'
            );
            BookingStatusHistory::create($data);
            $message = $user->firstname . ' has rescheduled your booking, because ' . $request['reason_of_rescheduling'];
            //send notifiation if booking is rescheduled
            Notification::createNotification($booking->id, Notification::rescheduleBooking, Notification::rescheduleBookingTitle, $message, $send_notification_to);
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/service.booking_rescheduled_successfully');
            return response()->json($this->response, 200);
        }
    }

    // function to send data for reschsduling
    public function getRescheduleData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }

        $booking = Booking::where('id', '=', $request['booking_id'])->first();

        // if vendor booking on hold then then it can't be extends request  
        // if booking status will be on hold then request terminated here with proper message
        if ($booking->is_onhold == '1') {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.booking_on_hold');
            return response()->json($this->response, 403);
        }

        $today_date = date('Y-m-d 00:00:00');

        $get_date = $booking->booking_date;
        if (isset($request['booking_date']) && $request['booking_date'] != '') {
            $get_date = date('Y-m-d 00:00:00', strtotime($request['booking_date']));
        }
        $status_id = array(Booking::venderAssigned, Booking::venderOnTheWay, Booking::orderInProgres, Booking::venderArived);
        $booked_slots = BookedSlot::select('slot_id')->where([['vender_id', '=', $booking->vender_id], ['booking_date', '=', $get_date]])->whereIn('status_id', $status_id)->get();
        $return_slots = array();
        $i = 0;
        if ($booking->vender_id) {

            $get_vender_slot = venderSlot::select('slot_id')->where('vender_id', '=', $booking->vender_id)->get();
            foreach ($get_vender_slot as $slot) {

                $s['id'] = $slot->slot->id;
                $s['day'] = $slot->slot->day;
                $s['slotFrom'] = date('H:i', strtotime($slot->slot->slot_from));
                $s['slotTo'] = date('H:i', strtotime($slot->slot->slot_to));
                $return_slots[$slot->slot->day][] = $s;
                $i++;
            }
        }

        $x = 1;
        for ($x = 1; $x <= 7; $x++) {
            if (!array_key_exists($x, $return_slots)) {
                $return_slots[$x] = array();
            }
        }
        $bk_slots = array();

        foreach ($booked_slots as $booked_slot) {
            $bk_slots[] = $booked_slot['slot_id'];
        }
        $status = 1;
        if (empty($bk_slots) && empty($return_slots)) {
            $status = 0;
        } else {
            $status = 1;
        }

        $this->response['status'] = $status;
        $this->response['data']->hours = $booking->selected_hours;
        $this->response['data']->allSlots = $return_slots ? $return_slots : (object) $return_slots;
        $this->response['data']->bookedSlots = $bk_slots;
        $this->response['message'] = trans('api/service.avail_booked_slots');
        return response()->json($this->response, 200);
    }

    //function to list booking request on vender end on base of list type parameter 
    //0 = all request, 
    //1 = all accepted and in progress, 
    //2 = all completed cancelled and refunded

    public function vendorBookingsList(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'list_type' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $booking_data = array();
        $user = Auth::User();
        $status = $request['list_type'];
        $status_id = '';
        $booking = '';
        $bookings = '';
        if ($status == 0) {

            $booking_ids = Notification::select('type_id')->where([['user_id', '=', $user->id], ['title', '=', Notification::bookingService], ['rejected_booking', '=', NULL]])->get();
            $new_array = array();
            if ($booking_ids->count()) {
                foreach ($booking_ids as $array) {
                    array_push($new_array, $array->type_id);
                }
            }
            $bookings = Booking::where('status_id', '=', Booking::orderPlaced)->whereIn('id', $new_array)->orderBy('created_at', 'DESC')->paginate(self::totalRow);
        }

        if ($status == 1) {

            $status_id = array(Booking::venderAssigned, Booking::venderOnTheWay, Booking::orderInProgres, Booking::venderArived);
            $date = date('Y-m-d');
            if (isset($request['selected_date']) && $request['selected_date'] != '') {
                $date = $request['selected_date'];
                $bookings = Booking::Where([['vender_id', '=', $user->id], ['booking_date', '=', $date]])->whereIn('status_id', $status_id)->orderBy('created_at', 'DESC')->paginate(self::totalRow);
            } else {
                $bookings = Booking::Where([['vender_id', '=', $user->id]])->whereIn('status_id', $status_id)->orderBy('created_at', 'DESC')->paginate(self::totalRow);
            }
        }

        if ($status == 2) {
            $date = date('Y-m-d');
            $status_id = array(Booking::orderCompleted, Booking::orderRefund, Booking::orderCanceled);
            $bookings = Booking::where([['vender_id', '=', $user->id]])->whereIn('status_id', $status_id)->orderBy('created_at', 'DESC')->paginate(self::totalRow);
        }
        $i = 0;

        if ($bookings) {
            foreach ($bookings as $booking) {

                $booking_data[$i]['userId'] = $booking->user_id;
                $booking_data[$i]['userName'] = $booking->user->firstname . ' ' . $booking->user->lastname;
                $avtar_image = '';
                if ($booking->user->avtaar_image) {
                    $avtar_image = AvatarImage::select('image_name')->where('id', $booking->user->avtaar_image)->first();
                }
                $booking_data[$i]['userProfilePic'] = isset($avtar_image->image_name) ? url('assets/avatar/' . $avtar_image->image_name) : '';
                $booking_data[$i]['serviceName'] = $booking->service_name;
                $booking_data[$i]['serviceId'] = $booking->service_id;
                $booking_data[$i]['bookingDate'] = date('Y-m-d', strtotime($booking->booking_date));
                $booking_data[$i]['bookingSlot'] = $booking->slots_time ?? '';
                $booking_data[$i]['bookingId'] = $booking->id;
                $booking_data[$i]['jobDescription'] = $booking->job_description ? $booking->job_description : '';
                $booking_data[$i]['equipments'] = $booking->additional_equipments ? $booking->additional_equipments : '';
                $booking_data[$i]['additionalEquipments'] = $booking->bookedEquipment ? BokedEquip::collection($booking->bookedEquipment) : array('');
                $booking_data[$i]['bookingPrice'] = $booking->total_price;
                $booking_data[$i]['paymentPending'] = $user->pending_payment ? $user->pending_payment : 0;
                $reject_status = Notification::where(['user_id' => $user->id, 'type_id' => $booking->id, 'title' => Notification::bookingService])->first();
                if ($request['list_type'] == 0) {
                    $check_not = Notification::where('type_id', '=', $booking->id)->orderBy('id', 'desc')->first();
                    if (($booking->status_id == Booking::orderPlaced)) {

                        $booking_data[$i]['action'] = 1;
                    }


                    if ($booking->status_id == Booking::orderPlaced && $check_not && $check_not->type == '5') {
                        $booking_data[$i]['action'] = 13;
                    }

                    if (isset($booking->vender_id)) {
                        if ($booking->vender_id != $user->id) {
                            $booking_data[$i]['action'] = 4;
                        }
                    }
                }
                if ($request['list_type'] == 1) {

                    if ($booking->status_id == Booking::venderAssigned || $booking->status_id == Booking::venderOnTheWay || $booking->status_id == Booking::orderInProgres || $booking->status_id == Booking::venderArived) {
                        $booking_data[$i]['action'] = 2;
                    }

                    $getExtendHour = ExtraHour::where([['booking_id', '=', $booking->id], ['status', '=', '0']])->first();
                    $getCompletedExtendHour = ExtraHour::where([['booking_id', '=', $booking->id], ['status', '=', '1']])->first();
                    $getRejectedExtendHour = ExtraHour::where([['booking_id', '=', $booking->id], ['status', '=', '2']])->first();

                    if ($getExtendHour) {
                        $booking_data[$i]['action'] = 5;
                    }
                    if ($getCompletedExtendHour) {

                        $booking_data[$i]['action'] = 6;
                    }
                    if ($getRejectedExtendHour) {

                        $booking_data[$i]['action'] = 7;
                    }
                    $currentDate = strtotime(date('Y-m-d H:i:s'));
                    $bookingDateTime = date('Y-m-d', strtotime($booking->booking_date)) . ' ' . $booking->slot_start_from;
                    if ($currentDate > strtotime($bookingDateTime)) {
                    }
                    $bookingAcceptedDate = strtotime($booking->accepted_at);
                    $remaningTime = $currentDate - $bookingAcceptedDate;
                    $booking_data[$i]['canCancel'] = true;
                    $status_id = array(Booking::venderOnTheWay, Booking::orderInProgres, Booking::venderArived);


                    if ($remaningTime > self::cancelation_time || in_array($booking->status_id, $status_id)) {

                        $booking_data[$i]['canCancel'] = false;
                    }
                }
                if ($request['list_type'] == 2) {

                    if ($booking->status_id == Booking::venderAssigned) {
                        $booking_data[$i]['action'] = 5;
                    }
                    if ($booking->status_id == Booking::orderCompleted) {
                        $booking_data[$i]['action'] = 8;
                    }
                    if ($booking->status_id == Booking::orderCanceled) {
                        $booking_data[$i]['action'] = 10;
                    }
                    if ($booking->status_id == Booking::orderRefund) {
                        $booking_data[$i]['action'] = 9;
                    }
                    $booking_data[$i]['serviceImage'] = $booking->service->image ? url('/assets/services') . '/' . $booking->service->image : '';
                }
                $i++;
            }
        }
        $message = trans('api/service.booking_detail');
        if (!$booking_data) {
            $message = trans('api/service.no_booking_found');
        }
        $this->response['message'] = $message;
        $this->response['data'] = $booking_data;
        $this->response['status'] = 1;
        return response()->json($this->response, 200);
    }

    //function to extend booking duration
    public function extentionRequestToUser(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'booking_id' => ['required'],
            'minutes' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $user = Auth::User();
        $booking_id = $request['booking_id'];
        $requested_time = $request['minutes'];
        $booking = Booking::where('id', '=', $booking_id)->first();

        // if vendor booking on hold then then it can't be extends request  
        // if booking status will be on hold then request terminated here with proper message
        if ($booking->is_onhold == '1') {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.booking_on_hold');
            return response()->json($this->response, 403);
        }

        if ($booking->vender_id != $user->id) {
            $this->response['message'] = trans('api/service.you_are_not_owner_of_this_booking');;
            $this->response['status'] = 0;
            return response()->json($this->response, 401);
        }

        if ($booking) {
            $today_date = date('Y-m-d H:i:s');
            $hours = $requested_time;
            $extra_hours_data = array(
                "booking_id" => $booking_id,
                "extended_minutes" => $requested_time,
                "extention_accepted_at" => $today_date,
            );
            ExtraHour::create($extra_hours_data);
            $data = array(
                'booking_id' => $booking->id,
                'status_id' => Booking::extentionPending,
                'user_type' => 'vendor'
            );
            BookingStatusHistory::create($data);
            $message = 'Hi ' . $booking->user->firstname . ' ' . $booking->user->lastname . ',  You have received the request to extend job time by ' . $booking->vender->firstname . '.';
            Notification::createNotification($booking->id, Notification::extendTimeNotification, Notification::extendJobTime, $message, $booking->user_id);
            $this->response['message'] = trans('api/service.thanks_for_request_we_will_notify_once_request_approved');;
            $this->response['status'] = 1;
            return response()->json($this->response, 200);
        }
    }

    //
    public function updateExtendTimeRequest(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => ['required'],
            'status' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $user = Auth::User();
        $id = $request['id'];
        $extraHour = ExtraHour::where('id', '=', $id)->first();
        $booking = Booking::where('id', '=', $extraHour->booking_id)->first();

        // if vendor booking on hold then then it can't be extends request  
        // if booking status will be on hold then request terminated here with proper message
        if ($booking->is_onhold == '1') {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.booking_on_hold');
            return response()->json($this->response, 403);
        }

        if ($booking->user_id != $user->id) {
            $this->response['message'] = trans('api/service.you_have_not_made_this_booking');
            $this->response['status'] = 0;
            return response()->json($this->response, 401);
        }
        $extraHour->status = (string) $request['status'];
        if ($extraHour->save()) {
            $ext_status = Booking::extentionCompleted;
            $message = 'Hi ' . $booking->vender_name . ' ' . $booking->vender->lastname . ', Your extension request has been accepted by ' . $booking->user->firstname . ' ' . $booking->user->lastname . '.';
            if ($request['status'] == 2) {
                $message = 'Hi ' . $booking->vender->firstname . ' ' . $booking->vender->lastname . ', Your extension request has been rejectd by ' . $booking->user->firstname . ' ' . $booking->user->lastname . '.';
                $ext_status = Booking::extentionRejected;
            }

            $data = array('booking_id' => $booking->id, 'status_id' => $ext_status, 'user_type' => 'user');
            BookingStatusHistory::create($data);
            Notification::createNotification($booking->id, Notification::extendTimeNotification, Notification::extendJobTime, $message, $booking->vender_id);

            $this->response['message'] = trans('api/service.thanks_for_your_responce');
            $this->response['status'] = 1;
            return response()->json($this->response, 200);
        }
    }

    public function getAppointments(Request $request)
    {

        $user = Auth::User();

        $status_id = array(Booking::venderAssigned, Booking::venderOnTheWay, Booking::orderInProgres, Booking::venderArived);
        $startDate = $request['start_date'];
        $endDate = $request['end_date'];

        $time = date('H:i');

        $selected_date = '';
        if ((isset($request['start_date']) && $request['start_date'] != '') && (isset($request['end_date']) && $request['end_date'] != '')) {
            $date = $request['selected_date'];
            $bookings = Booking::Where([['vender_id', '=', $user->id], ['booking_date', '>=', $startDate], ['booking_date', '<=', $endDate]])->whereIn('status_id', $status_id)->orderBy('created_at', 'DESC')->paginate(self::totalRow);
        } else {
            $bookings = Booking::Where([['vender_id', '=', $user->id], ['booking_date', '>=', $startDate], ['booking_date', '<=', $endDate]])->whereIn('status_id', $status_id)->orderBy('created_at', 'DESC')->paginate(self::totalRow);
        }
        $i = 0;
        $booking_data = array();
        foreach ($bookings as $booking) {

            if ($date != date('Y-m-d', strtotime($booking->booking_date))) {
                $i = 0;
            }

            $date = date('Y-m-d', strtotime($booking->booking_date));




            $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['userId'] = $booking->user_id;
            $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['userName'] = $booking->user->firstname . ' ' . $booking->user->lastname;
            $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['userProfilePic'] = ($booking->user->image) ? url('images/avatars/' . $booking->user->image) : '';
            $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['serviceName'] = $booking->service_name;
            $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['serviceId'] = $booking->service_id;
            $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['bookingDate'] = date('Y-m-d', strtotime($booking->booking_date));
            $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['bookingSlot'] = date('H:i', strtotime($booking->slot_start_from)) . '-' . date('H:i', strtotime($booking->slot_start_end));
            $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['bookingId'] = $booking->id;
            $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['jobDescription'] = $booking->job_description ? $booking->job_description : '';
            $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['additionalEquipments'] = $booking->additional_equipments ? $booking->additional_equipments : '';
            $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['bookingPrice'] = $booking->total_price;
            $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['paymentPending'] = $booking->user->pending_payment ? $booking->user->pending_payment : 0;
            $reject_status = Notification::where(['user_id' => $user->id, 'type_id' => $booking->id, 'title' => Notification::bookingService])->first();



            if ($booking->status_id == Booking::venderAssigned || $booking->status_id == Booking::venderOnTheWay || $booking->status_id == Booking::orderInProgres || $booking->status_id == Booking::venderArived) {
                $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['action'] = 2;
            }

            $getExtendHour = ExtraHour::where([['booking_id', '=', $booking->id], ['status', '=', '0']])->first();
            $getCompletedExtendHour = ExtraHour::where([['booking_id', '=', $booking->id], ['status', '=', '1']])->first();
            $getRejectedExtendHour = ExtraHour::where([['booking_id', '=', $booking->id], ['status', '=', '2']])->first();

            if ($getExtendHour) {
                $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['action'] = 5;
            }
            if ($getCompletedExtendHour) {

                $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['action'] = 6;
            }
            if ($getRejectedExtendHour) {

                $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['action'] = 7;
            }
            $currentDate = strtotime(date('Y-m-d H:i:s'));
            $bookingDateTime = date('Y-m-d', strtotime($booking->booking_date)) . ' ' . $booking->slot_start_from;
            if ($currentDate > strtotime($bookingDateTime)) {

                $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['action'] = 11;
            }
            $bookingAcceptedDate = strtotime($booking->accepted_at);
            $remaningTime = $currentDate - $bookingAcceptedDate;
            $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['canCancel'] = true;
            $status_id = array(Booking::venderOnTheWay, Booking::orderInProgres, Booking::venderArived);
            if ($remaningTime > self::cancelation_time || in_array($booking->status_id, $status_id)) {

                $booking_data[date('Y-m-d', strtotime($booking->booking_date))][$i]['canCancel'] = false;
            }


            $i++;
        }
        $message = trans('api/service.booking_detail');
        if (!$booking_data) {
            $message = trans('api/service.no_booking_found');
        }
        $this->response['message'] = $message;
        $this->response['data'] = $booking_data ? $booking_data : (object) $booking_data;
        $this->response['status'] = $booking_data ? 1 : 0;
        return response()->json($this->response, 200);
    }

    public function addReview(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'booking_id' => ['required'],
            'rating' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $user = Auth::User();

        $booking = Booking::where('id', $request['booking_id'])->first();

        // if booking not found then jsut return the response by ending the processs
        if (!$booking) {
            $this->response['message'] = trans('api/service.booking_not_exist');
            $this->response['status'] = 0;
            return response()->json($this->response, 403);
        }

        $data = array();
        if ($booking) {
            if (!$booking->vender_id) {
                $this->response['message'] = trans('api/service.cant_rate_this_booking_vender_not_assigned');
                $this->response['status'] = 0;
                return response()->json($this->response, 403);
            }
            $data['user_id'] = $booking->user_id;
            $data['vender_id'] = $booking->vender_id;
            $data['booking_id'] = $booking->id;
            $data['rating'] = $request['rating'];
            $data['is_like'] = $request['is_like'] ? $request['is_like'] : '';
            $submitted_by = $user->id;


            if ($user->hasRole('vendor')) {
                $image = isset($user->image) ? $user->image : "";
                $submitted_to = $booking->user_id;
            } else {
                $submitted_to = $booking->vender_id;
                $avatar_image = AvatarImage::where('id', $user->avtaar_image)->first();
                $image = isset($avatar_image->image_name) ? $avatar_image->image_name : "";
            }

            $data['review_submitted_by'] = $submitted_by;
            $data['review_submitted_to'] = $submitted_to;
            $data['review_type'] = 'booking';
            $data['submitter_image'] = $image;
            $data['feedback_message'] = $request['feedback_message'];
        }

        $checkAlreadyRated = Review::where([['booking_id', '=', $booking->id], ['review_submitted_by', '=', $user->id]])->first();
        if ($checkAlreadyRated) {
            $this->response['message'] = trans('api/service.already_rated');
            $this->response['status'] = 0;
            return response()->json($this->response, 200);
        }

        if (Review::create($data)) {
            if ($user->hasRole('user')) {
                $number_of_reviews = Review::where('review_submitted_to', $booking->vender_id)->get()->count();
                // if vendor have above 25 reviews and 4.8 average rating then vendoor considered to be pro vendor
                $vendor = User::find($booking->vender_id);
                if ($number_of_reviews > 1) {
                    $newRating = (float)$vendor->rating + (float)$request['rating'];
                    $vendor->rating = $newRating;
                    $average_rating = $newRating / (int)$number_of_reviews; //get the average rating of the vendor
                    // check if vendor is pro or not if pro then change the isPro field in databse other wise not
                    if ($number_of_reviews >= Review::reviews_for_pro && $average_rating >= Review::average_rating_for_pro) {
                        $vendor->isPro = "1";
                    } else {
                        $vendor->isPro = "0";
                    }
                } else {
                    $vendor->rating = $request['rating'];
                }
                $vendor->save();
            }
            $this->response['message'] = trans('api/service.rated_successfully');
            $this->response['status'] = 1;
            return response()->json($this->response, 200);
        }
    }

    private function generate_random_string()
    {
        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
            . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . '0123456789'); // and any other characters
        shuffle($seed); // probably optional since array_is randomized; this may be redundant
        $rand = '';
        foreach (array_rand($seed, 5) as $k)
            $rand .= $seed[$k];

        return $rand;
    }

    // @param  booking_id ; - booking id of anybookg whcih is passed in request

    /* private function checkHoldStatus($booking_id) {
      // if vendor booking on hold then then it can't be extends request
      $booking = Booking::find($booking_id);

      if ($booking->is_onhold == 1) {
      return true;
      }
      } */

    public function notifyMe(NotifyMeRequest $request)
    {
        $user = Auth::user();
        $notify = NotifyMe::whereUser_id($user->id)->whereService_id($request->service_id)->first();
        if ($notify) {
            $this->response['message'] = trans('api/service.already_subscribed_for_notify');
            $this->response['status'] = 0;
            return response()->json($this->response, Response::HTTP_UNAUTHORIZED);
        }
        $user_selected_address = $user->userAddress()->whereId($request->address_id)->first();
        if (!$user_selected_address) {
            $this->response['message'] = trans('api/service.you_have_selected_wrong_address_id');
            $this->response['status'] = 0;
            return response()->json($this->response, Response::HTTP_CONFLICT);
        }

        NotifyMe::create($this->getNotifyMeInsertData($user->id, $request->service_id, $user_selected_address));
        $this->response['message'] = trans('api/service.you_have_subscribed_for_service_notification');
        $this->response['status'] = 1;
        return response()->json($this->response, Response::HTTP_CREATED);
    }
    private function getNotifyMeInsertData($user_id, $service_id, $selected_address)
    {
        return [
            'user_id' => $user_id,
            'service_id' => $service_id,
            'address_id' => $selected_address->id,
            'address_latitude' => $selected_address->latitude,
            'address_longitude' => $selected_address->longitude,
        ];
    }

}
