<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Service;
use App\slot;
use App\User;
use App\venderSlot;
use App\UserAddresses;
use App\ServiceCategory;
use App\Booking;
use App\Coupon;
use App\userCoupon;
use App\CouponHistory;
use App\Slider;
use App\VenderService;
use App\BookingDetail;
use App\Http\Requests\CheckServiceAvailabilityRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\ServiceCollection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Coupon as CouponResource;
use App\Http\Resources\CouponListCollection;
use App\Http\Resources\CouponList;
use App\Http\Resources\BookingCollection;
use App\Http\Resources\Banner as BannerResource;
use App\Http\Resources\BannerCollection;
use App\ServiceFrequency;
use Illuminate\Support\Facades\DB;

class ServicesController extends Controller
{

    protected $response = [
        'status' => 0,
        'message' => '',
    ];

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

    public function index(Request $request)
    {

        $services = '';
        $all_servies = Service::select('cat_id')->where('status',  Service::active_serivce)->distinct('cat_id')->get()->toArray();
        $cat_ids = array_column($all_servies, 'cat_id');
        $services = ServiceCategory::where(['status' => '1'])->whereIn('id', $cat_ids)->get();
        return (new CategoryCollection($services))->additional([
            'status' => 1,
            'message' => trans('api/service.total_record')
        ]);
    }

    // public function get_all_services(Request $request)
    // {

    //     $this->response['data'] = array();
    //     $user = Auth::User();
    //     $current_address = $user->selected_address;
    //     if (!$current_address) {
    //         $this->response['status'] = 0;
    //         $this->response['message'] = trans('api/service.please_select_default_address');
    //         return response()->json($this->response, 404);
    //     }
    //     $searching_area_in_km = env('SEARCHING_AREA', 20);
    //     $adress = UserAddresses::where('id', '=', $current_address)->first();
    //     $lat = $adress->latitude;
    //     $long = $adress->longitude;
    //     $vender_in_near_area = DB::table('user_addresses')
    //     // Just uncomment it if you need services based on user address
    //         // ->select(DB::raw(" *, ( 6371 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($long) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) ) AS distance "))
    //         // ->havingRaw("distance <=  $searching_area_in_km")
    //         ->join('users', 'user_addresses.user_id', '=', 'users.id')
    //         ->join('vender_services', 'users.id', '=', 'vender_services.vender_id')
    //         ->join('role_user', 'users.id', '=', 'role_user.user_id')
    //         ->where('role_user.role_id', '=', 2)
    //         ->where('users.online', '=', '1')
    //         ->where('users.stripe_id', '!=', '0')
    //         ->where('users.status', '=', '1')
    //         ->get();

    //     $final_venders = '';
    //     $ids = array();
    //     foreach ($vender_in_near_area as $vender) {

    //         $ids[] = $vender->user_id;
    //     }

    //     $vendor_ids = array_values(array_unique($ids));

    //     $service_ids = VenderService::select('cat_id', 'vender_id')->whereIn('vender_id', $vendor_ids)->get()->toArray();
    //     $all_services = array_map("unserialize", array_unique(array_map("serialize", $service_ids)));


    //     $service_data = [];
    //     foreach ($all_services as $service) {
    //         if (array_key_exists($service['cat_id'], $service_data)) {
    //             $service_data[$service['cat_id']]['cat_id'] = $service['cat_id'];
    //             $service_data[$service['cat_id']]['vender_id'] = $service_data[$service['cat_id']]['vender_id'] . "," . $service['vender_id'];
    //         } else {
    //             $service_data[$service['cat_id']]['cat_id'] = $service['cat_id'];
    //             $service_data[$service['cat_id']]['vender_id'] = $service['vender_id'];
    //         }
    //     }

    //     $data = [];
    //     foreach ($service_data as $all_service) {
    //         $service_data = ServiceCategory::where('id', '=', $all_service['cat_id'])->where(['status' => ServiceCategory::active_service_catagory])->first();
    //         if ($service_data) {
    //             $vendors = explode(',', $all_service['vender_id']);
    //             $getServiceids = VenderService::select('service_id')->where('cat_id', $all_service['cat_id'])->whereIn('vender_id', $vendors)->get()->toArray();

    //             $idArray = array_column($getServiceids, 'service_id');
    //             $service = Service::whereIn('id', $idArray)->where('status', Service::active_serivce)->get();
    //             // if subservice exist then show te whole service otherwise blank
    //             if ($service->count() > 0) {
    //                 $data[$all_service['cat_id']]['id'] = $service_data->id;
    //                 $data[$all_service['cat_id']]['serviceName'] = $service_data->cat_name;
    //                 $data[$all_service['cat_id']]['image'] = url('assets/category/' . $service_data->image);
    //                 $data[$all_service['cat_id']]['subServices'] = new ServiceCollection($service);
    //             }
    //         }
    //     }
    //     if (!$data) {
    //         $this->response['status'] = 0;
    //         $this->response['message'] = trans('api/service.no_record_found');
    //         $this->response['data'] = $data;
    //         return response()->json($this->response, 404);
    //     }

    //     $this->response['status'] = 1;
    //     $this->response['message'] = trans('api/service.total_record');
    //     $this->response['data'] = array_values($data);
    //     return response()->json($this->response, 200);
    // }
    public function get_all_services(Request $request)
    {
        $this->response['data'] = array();
        $user = Auth::User();
        $data = [];
            $service_data = ServiceCategory::all();
            if ($service_data) {
                foreach ($service_data as $all_service) {
                    $service = Service::where('cat_id',$all_service->id )->get();
                    // if subse exist then show te whole service otherwise blank
                    // if ($service->count() > 0) {
                        $data[$all_service->id]['id'] = $all_service->id;
                        $data[$all_service->id]['serviceName'] = $all_service->cat_name;
                        $data[$all_service->id]['image'] = url('assets/category/' . $all_service->image);
                        $data[$all_service->id]['subServices'] = new ServiceCollection($service);
                    // }
                }
            }
        if (!$data) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.no_record_found');
            $this->response['data'] = $data;
            return response()->json($this->response, 404);
        }

        $this->response['status'] = 1;
        $this->response['message'] = trans('api/service.total_record');
        $this->response['data'] = array_values($data);
        return response()->json($this->response, 200);
    }

    public function listBanners()
    {
        $banners = Slider::orderBy('id', 'desc')->get();
        return (new BannerCollection($banners))->additional([
            'status' => 1,
            'message' => trans('api/service.all_banners')
        ]);
    }

    public function checkCouponCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $coupon = Coupon::where(['code' => $request['coupon_code']])->first();
        if (!$coupon) {
            $coupon = userCoupon::where(['code' => $request['coupon_code']])->first();
        }
        if (!$coupon) {
            $coupon = userCoupon::where(['id' => $request['coupon_id']])->first();
        }
        if (!$coupon) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.invalid_coupon');
            return response()->json($this->response, 404);
        }
        if (CouponHistory::where('coupon_id', $coupon->id)->get()->count() == $coupon->maxTotalUse) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/service.coupons_used');
            return response()->json($this->response, 404);
        }

        if ($request['coupon_id'] == 0) {
            if (!$coupon->status) {
                $this->response['status'] = 0;
                $this->response['message'] = trans('api/service.coupon_is_not_active');
                return response()->json($this->response, 401);
            }
            $date = strtotime(date('Y-m-d'));
            $start_date = strtotime(date($coupon->startDateTime));
            $end_date = strtotime(date($coupon->endDateTime));
            if ($date < $start_date || $date > $end_date) {
                $this->response['status'] = 0;
                $this->response['message'] = trans('api/service.coupon_is_expired');
                return response()->json($this->response, 401);
            }
            if ($request['order_amount'] < $coupon->minAmount) {
                $this->response['status'] = 0;
                $this->response['message'] = trans('api/service.order_amount_is_less_than_required') . $coupon->minAmount;
                return response()->json($this->response, 401);
            }
        }
        return (new CouponList($coupon))->additional([
            'status' => 1,
            'message' => trans('api/service.valid_coupon')
        ]);
    }

    public function couponApplied(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'coupon_id' => ['required'],
            'discount_amount' => ['required'],
            'coupon_code' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $coupon = Coupon::where(['code' => $request['coupon_code']])->first();
        if (!$coupon) {
            $coupon = userCoupon::where(['code' => $request['coupon_code']])->first();
        }
        if (!$coupon) {
            $coupon = userCoupon::where(['id' => $request['coupon_id']])->first();
        }
        if (CouponHistory::where(['coupon_id' => $coupon->id])->get()->count() == $coupon->maxTotalUse) {
            $this->response['status'] = 0;
            $this->response['message'] =  trans('api/service.coupons_used');
            $this->response['data'] = array();
            return response()->json($this->response, 200);
        }
        $user = Auth::User();
        $data = $request->all();
        $data['user_id'] = $user->id;
        $coupon_history_id = CouponHistory::create($data);
        $this->response['status'] = 1;
        $this->response['message'] = trans('api/service.coupon_history_created');
        $this->response['data'] = array('couponHistoryId' => $coupon_history_id->id);
        return response()->json($this->response, 200);
    }

    public function getCoupon()
    {
        // check if user coupon expired or not if expired then insert the id in the array
        $all_global_coupons = Coupon::where('status', 1)->get();
        $expired_coupons_id = array();
        foreach ($all_global_coupons as $coupon) {
            // check if global coupon expired or not if expired then insert the id in the array
            if (CouponHistory::where('coupon_id', $coupon->id)->get()->count() == $coupon->maxTotalUse) {
                $expired_coupons_id[] = $coupon->id;
            }
        }
        $valid_global_coupons = Coupon::where('status', 1)->whereNotIn('id',  $expired_coupons_id)->get();
        $user = Auth::User();

        // get all the user coupons 
        $user_all_coupon = userCoupon::where([['user_id', '=', $user->id],  ['status', '=', '0']])->get();

        $expired_user_coupons = array();
        foreach ($user_all_coupon as $coupon) {
            // check if user coupon expired or not if expired then insert the id in the array
            if (CouponHistory::where(['coupon_id' => $coupon->id])->get()->count() == $coupon->maxTotalUse) {
                $expired_user_coupons[] = $coupon->id;
            }
        }
        //  get the user coupons except for which they are expired
        $valid_user_coupons = userCoupon::where([['user_id', '=', $user->id], ['status', '=', '0']])->whereNotIn('id',  $expired_user_coupons)->get();

        return (new CouponListCollection($valid_global_coupons))->additional([
            'status' => 1,
            'userCoupon' => new CouponListCollection($valid_user_coupons),
            'message' => trans('api/service.all_coupon')
        ]);
    }
    public function checkServiceAvailability(CheckServiceAvailabilityRequest $request)
    {
        $userAddress = UserAddresses::find($request->address_id);

        $searching_area_in_km = env('SEARCHING_AREA', 20);
        $lat = $userAddress->latitude;
        $long = $userAddress->longitude;

        $vender_in_near_area = DB::table('user_addresses')
            ->select(DB::raw(" *, ( 6371 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($long) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) ) AS distance "))
            ->havingRaw("distance <=  $searching_area_in_km")
            ->join('users', 'user_addresses.user_id', '=', 'users.id')
            ->join('vender_services', 'users.id', '=', 'vender_services.vender_id')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->where('role_user.role_id', '=', 2)
            ->where('users.stripe_id', '!=', '0')
            ->where('users.status', '=', '1')
            ->groupBy('users.id')
            ->get();

            foreach($vender_in_near_area as $vendor){
                $ifAvailable = VenderService::where(['vender_id' =>  $vendor->id, 'service_id' => $request->service_id ])->first();
                if($ifAvailable){
                    break;
                }
            }
        $this->response['status'] = 0;
        $this->response['message'] = trans('api/service.service_not_found');
        if ($ifAvailable) {
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/service.service_found');
        }
        return response()->json($this->response, 200);
    }
}
