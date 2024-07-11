<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\venderSlot;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\VenderSlots as VenderSlotsResource;
use App\Http\Resources\SlotCollection;
use App\Http\Resources\VenderSlotsCollection;
use App\slot;
use App\User;
use App\Booking;
use App\BookedSlot;
use App\Service;
use App\VenderService;

class venderSlotController extends Controller
{

    protected $response = [
        'status' => 0,
        'message' => '',
    ];

    public function __construct()
    {
        $this->response['data'] = new \stdClass();
    }

    public function addVenderSlot(Request $request)
    {

        /* $validator = Validator::make($request->all(), [
          'slot_id' => ['required']
          ]);
          if ($validator->fails()) {
          $this->response['message'] = $validator->errors()->first();
          return response()->json($this->response, 401);
          } */
        $user = Auth::User();
        if (!$user->hasRole(['vendor'])) {

            $this->response['message'] = trans('api/user.user_is_not_vender');
            return response()->json($this->response, 401);
        }
        $count = 0;
        if ($request['slot_id']) {

            $check_slot_already_assigned = venderSlot::where('vender_id', '=', $user->id)->get();
            $assigned_array = array();
            foreach ($check_slot_already_assigned as $check_slot_already_assin) {
                $assigned_array[] = $check_slot_already_assin['slot_id'];
            }
            $array1 = array_diff($assigned_array, $request['slot_id']);
            $array2 = array_diff($request['slot_id'], $assigned_array);
            $array3 = array_merge($array1, $array2);
            $allSlots = venderSlot::where(['vender_id' => $user->id])->get();
            if (!count($array3)) {
                return (new VenderSlotsCollection($allSlots))->additional([
                    'status' => 1,
                    'message' => trans('api/service.no_change_in_slot')
                ]);
            }
            foreach ($array3 as $slot_id) {

                $if_alreay_assigned_then_remove = venderSlot::where(['slot_id' => $slot_id, 'vender_id' => $user->id])->first();
                if (isset($if_alreay_assigned_then_remove->id)) {
                    $if_alreay_assigned_then_remove->delete();
                } else {
                    $addVenderSlot = venderSlot::create(['vender_id' => $user->id, 'slot_id' => $slot_id]);
                    $count++;
                }
            }
            $allSlots = venderSlot::where(['vender_id' => $user->id])->get();
            return (new VenderSlotsCollection($allSlots))->additional([
                'status' => 1,
                'message' => trans('api/service.slots_added_successfully')
            ]);
        } else {

            $if_alreay_assigned_then_remove = venderSlot::where(['vender_id' => $user->id])->first();
            if ($if_alreay_assigned_then_remove) {
                $if_alreay_assigned_then_remove->delete();
            }
            $this->response['message'] = trans('api/service.no_new_slot_added');
            return response()->json($this->response, 200);
        }
    }

    public function getSlots(Request $request)
    {
        if (isset($request->price_type)) {
            $validator = Validator::make($request->all(), [
                'price_type' => 'required|in:0,1'
            ]);
            if ($validator->fails()) {
                $this->response['message'] = $validator->errors()->first();
                return response()->json($this->response, 401);
            }
        }

        $service_id = $request['service_id'];
        $price_type = $request['price_type'];
        $vender_id = $request['vender_id'];
        $user_type = (isset($request['user_type'])) ? $request['user_type'] : 'user';

        $all_slots = '';
        $user = Auth::user();

        // blank slots array
        $blank_slots_data = [];
        for ($i = 1; $i <= 7; $i++) {
            $blank_slots_data[$i] = array();
        }

        // condition for pro and standard vendors according to the prize type  
        if ($price_type != '' && $price_type == 1) {
            $condition = '=';
        } else {
            $condition = '!=';
        }
        // -------------------------for user end api hit start----------------------------------
        if ($user->hasRole('user')) {

            if (isset($vender_id) && $vender_id != '' && $vender_id != '0') {
                $user = User::where('id', $request['vender_id'])->first();
                if (!$user) {
                    $this->response['status'] = 0;
                    $this->response['message'] = trans('api/user.vendor_not_found');
                    $this->response['data'] = $blank_slots_data;
                    return response()->json($this->response, 404);
                }
            }
            $all_slots = slot::select('slots.id as id', 'slots.day as day', 'slots.slot_from as slot_from', 'slots.slot_to as slot_to')->distinct('id')
                ->join('vender_slots', 'slots.id', '=', 'vender_slots.slot_id')
                ->join('vender_services', 'vender_services.vender_id', '=', 'vender_slots.vender_id')
                ->join('users', 'vender_slots.vender_id', '=', 'users.id');
            // if vendor id would be zero then find all the vendors corresponding to the service id 

            if ($vender_id == '0') {
                $all_slots = $all_slots->where('vender_services.service_id', '=', $service_id);
            }
            // if there is request for the vendors then get that particular vendor
            if (isset($vender_id) && $vender_id != '' && $vender_id != '0') {
                $all_slots = $all_slots->where('users.id', '=', $vender_id);
            } else {
                // get all the vendor on the particular location
                $selected_address = $user->selectedAddress;
                $searching_area_in_km = env('SEARCHING_AREA', 20);
                $users = User::whereHas('selectedAddress', function ($address) use ($selected_address, $searching_area_in_km) {
                    $address->whereRaw("( 6371 * acos ( cos ( radians(" . $selected_address->latitude . ") ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(" . $selected_address->longitude . ") ) + sin ( radians(" . $selected_address->latitude . ") ) * sin( radians( latitude ) ) ) <= " . $searching_area_in_km . ")");
                })->get();

                $all_slots->whereIn('users.id', $users->pluck('id')->toArray());
            }

            $all_slots = $all_slots
                ->where("users.isPro", $condition, User::proUser)
                ->whereRAW('users.online = "1"')
                ->whereRAW('users.status = "1"')
                ->whereRAW('users.stripe_id != "0"')
                ->orderBy('slot_from', 'ASC')
                ->get();
        }
        // -------------------------for user end api hit end----------------------------------
        else {
            $all_slots = slot::select('id', 'day', 'slot_from', 'slot_to')->orderBy('slot_from', 'ASC')->get();
        }

        // if slots not found
        if (count($all_slots) == 0) {
            for ($i = 1; $i <= 7; $i++) {
                $return_slots[$i] = array();
            }

            $this->response['status'] = 0;
            $this->response['message'] = trans('api/user.slots_not_found');
            $this->response['data'] = $blank_slots_data;
            return response()->json($this->response, 404);
        }
        $return_slots = array();
        $user = Auth::user();
        $vendor_slots = array();
        $get_vender_slots = venderSlot::select('slot_id')->where(['vender_id' => $user->id])->get();
        if ($get_vender_slots) {
            foreach ($get_vender_slots as $get_vender_slot) {
                $vendor_slots[] = $get_vender_slot->slot_id;
            }
            $return_slots = array();
            $i = 0;
            foreach ($all_slots as $slot) {
                $selected = 0;
                if ($user->hasRole('vendor')) {
                    if (in_array($slot['id'], $vendor_slots)) {
                        $selected = 1;
                    }
                    $slot['selected'] = $selected;
                    $slot['slotFrom'] = date('H:i', strtotime($slot['slot_from']));
                    $slot['slotTo'] = date('H:i', strtotime($slot['slot_to']));
                    unset($slot['slot_from']);
                    unset($slot['slot_to']);
                    $return_slots[$slot->day][] = $slot;
                } else {
                    $slot['slotFrom'] = date('H:i', strtotime($slot['slot_from']));
                    $slot['slotTo'] = date('H:i', strtotime($slot['slot_to']));
                    unset($slot['slot_from']);
                    unset($slot['slot_to']);
                    $return_slots[$slot->day][] = $slot;
                }

                for ($i = 1; $i <= 7; $i++) {
                    if (empty($return_slots) || !array_key_exists($i, $return_slots)) {
                        $return_slots[$i] = array();
                    }
                }
            }
        }
        $this->response['status'] = 1;
        $this->response['message'] = 'All slots';
        $this->response['data'] = $return_slots;
        return response()->json($this->response, 200);
    }

    public function getBookedSlots(Request $request)
    {

        $service_id = $request['service_id'];
        $bookingDates = $request['dates'];
        $price_type = $request['price_type'];
        $venders = array();
        if (isset($request['vender_id'])) {
            if ($request['vender_id']) {
                $venders[] = $request['vender_id'];
            }
        }

        // get pro user limit

        if ($price_type == "1") {
            $condition = "=";
        } else {
            $condition = "!=";
        }
        $getVender = array();

        $getVender = VenderService::select('vender_id')
            ->join('users', 'vender_services.vender_id', '=', 'users.id')
            ->where('users.online', '=', '1')
            ->where("users.isPro", $condition,  User::proUser)
            ->where('users.status', '=', '1')
            ->where('service_id', '=', $service_id)
            ->get()->toArray();

        if (empty($venders)) {
            $venders = array_column($getVender, 'vender_id');
        }

        $final_array = array();
        $dates = array();
        if (empty($venders)) {
            foreach ($bookingDates as $bookingD) {
                $final_array[date('Y-m-d', ($bookingD))] = [];
            }
            //     $day = date('N', ($bookingD));
            //     $getSlotsOfDay = slot::select('id')->where('day', '=', $day)->get()->toArray();
            //     $allDays = array_column($getSlotsOfDay, 'id');
            //     $final_array[date('Y-m-d', $bookingD)] = $allDays;
            // }

            $this->response['status'] = 1;
            $this->response['message'] = trans('api/user.slots_not_found');
            $this->response['data'] = $final_array;
            return response()->json($this->response, 200);
        }


        $get_avail_slots = venderSlot::select('slot_id')->whereIn('vender_id', $venders)->get()->toArray();

        $get_avail_slots = array_column($get_avail_slots, 'slot_id');

        if (empty($get_avail_slots)) {

            foreach ($bookingDates as $bookingD) {
                $final_array[date('Y-m-d', ($bookingD))] = [];
            }
            //     $day = date('N', ($bookingD));
            //     $getSlotsOfDay = slot::select('id')->where('day', '=', $day)->get()->toArray();
            //     $allDays = array_column($getSlotsOfDay, 'id');
            //     $final_array[date('Y-m-d', $bookingD)] = $allDays;
            // }

            $this->response['status'] = 1;
            $this->response['message'] = trans('api/user.slots_not_found');
            $this->response['data'] = $final_array;
            return response()->json($this->response, 200);
        }

        $allslots = slot::all();
        $allDates = array();
        $fully_booked_slot = array();

        foreach ($bookingDates as $bookingDate) {
            $allDates[] = date('Y-m-d H:i:s', $bookingDate);
        }
        $bookedSlots = array();
        foreach ($allDates as $allDate) {
            $status_id = array(Booking::venderAssigned, Booking::venderOnTheWay, Booking::orderInProgres, Booking::venderArived);
            $bs = BookedSlot::select('slot_id')->whereIn('vender_id', $venders)->whereDate('booking_date', '=', date('Y-m-d 00:00:00', strtotime($allDate)))->groupby('slot_id')->get();
            $i = 0;

            foreach ($bs as $b) {

                $bookedSlots[$allDate][$i]['slot_id'] = $b->slot_id;
                $i++;
            }
        }

        foreach ($bookedSlots as $date => $bookedSlot) {
            foreach ($bookedSlot as $bkSlt) {
                $status_id = array(Booking::venderAssigned, Booking::venderOnTheWay, Booking::orderInProgres, Booking::venderArived);
                $booked_slot_count = BookedSlot::whereDate('booking_date', '=', date('Y-m-d 00:00:00', strtotime($date)))->where(['slot_id' => $bkSlt['slot_id']])->whereIn('status_id', $status_id)->whereIn('vender_id', $venders)->distinct('slot_id')->get();
                $bkSlot = $booked_slot_count->count();

                $get_available_venders = DB::table('vender_services')->select('vender_services.vender_id')->distinct()
                    ->join('vender_slots', 'vender_services.vender_id', '=', 'vender_slots.vender_id')
                    ->where('vender_services.service_id', '=', $request['service_id'])->get();


                //$get_available_venders = venderSlot::where(['slot_id' => $bkSlt['slot_id']]);
                $avail_vender = $get_available_venders->count();

                if ($bkSlot >= $avail_vender) {
                    $fully_booked_slot[date('Y-m-d', strtotime($date))][] = $bkSlt['slot_id'];
                }
            }
        }

        $dates_array = array();
        foreach ($bookingDates as $key => $bookingDate) {
            $dates_array[date('Y-m-d', $bookingDate)] = array();
        }
        $final_array = array_merge($dates_array, $fully_booked_slot);
        $this->response['status'] = 1;
        $this->response['message'] = trans('api/user.booked_slots');
        $this->response['data'] = $final_array;
        return response()->json($this->response, 200);
    }
}
