<?php

namespace App\Http\Controllers\API;

use App\favorite;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\VendorCollection;
use App\Http\Resources\Vendor;
use App\Http\Resources\ReviewCollection;
use App\Http\Resources\UserCollection;
use App\Models\Role;
use App\Review;
use App\venderSlot;
use App\User;
use App\Booking;
use App\slot;
use App\Traits\VendorTrait;
use App\UserPackage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Transaction;
use App\VenderService;
use Carbon\Carbon;

class vendorController extends Controller
{
    use \App\Traits\PaybaseApiTrait;
    use VendorTrait;

    private $status = array(6, 8, 12, 14);

    public function getAllVendors(Request $request)
    {


        try {

            $vendor_type = $request['vendor_type'] ? $request['vendor_type'] : 0;
            $where = '1';
            $default = 'ID';
            switch ($vendor_type) {
                case '0': // default top to bottom uproach (vendor have purchase and high ratings come first)
                    $default = ' `payment_status` DESC, `rating` DESC';
                    break;
                case '1':  //vip-vendors and have pro user
                    $where = ' `payment_status` = 1 AND `isPro` = ' . User::proUser;
                    break;
                case '2':  //VIP-vendors but not pro user
                    $where = ' `payment_status` = 1 AND `isPRO` = ' . User::notProUser;
                    break;
                case '3':  //pro vendors but not vip
                    $where = ' `payment_status` = 0 AND  `isPro` = ' . User::proUser;
                    break;
                case '4':  //standard vendors
                    $where = ' `payment_status` = 0 AND `isPRO` = ' . User::notProUser;
                    break;
            }
            $getVendors = array();

            // vendor listing on the selected date
            if ($request['filter_date'] != '') {

                $date = $request['filter_date'];
                $day = date('N', strtotime($date));
                $slotsOfDate = slot::where('day', $day)->get()->toArray();

                $getVendors = venderSlot::select('vender_id')->distinct('vender_id')->whereIn('slot_id', $slotsOfDate)->get()->toArray();


                foreach ($getVendors as $key => $getVendor) {

                    // get count of booked slot for particulr day
                    $checkBookedSlotOfVendor = \App\BookedSlot::where('vender_id', $getVendor['vender_id'])->where('booking_date', Date('Y-m-d 00:00:00'), $request['fileter_date'])->get()->count();

                    // get count of assigend slot for that day
                    $vendorSlotCount = venderSlot::where('vender_id', $getVendor['vender_id'])->whereIn('slot_id', $slotsOfDate)->get()->count();

                    if ($checkBookedSlotOfVendor >= $vendorSlotCount) {
                        unset($getVendors[$key]);
                    }
                }
            }

            // vendor listing on the selected vendor service
            if ($request['service_id'] != "") {
                $getVendors = VenderService::select('vender_id')->where(['service_id' => $request['service_id'], 'status' => '1'])->get()->toArray();
                if (count($getVendors) == 0) {;
                    return response(([
                        'status' => 0,
                        'message' => trans('api/user.vendor_not_found'),
                        'data' => array()
                    ]), 404);
                }
            }

            $vendors = array();
            if ($request['filter_date'] || $request['service_id']) {
                $vendor_ids = array_column($getVendors, 'vender_id');
                $vendors = User::whereIn('id', $vendor_ids)
                    ->where('status', '1')
                    ->where('online', User::online)
                    ->where('stripe_id', '!=', '0')
                    ->whereRaw($where)
                    ->orderByRaw($default)
                    ->paginate(20);
            } else {

                $vendors = User::whereHas('roles', function ($q) {
                    $q->where('role_id', '=', '2');
                })
                    ->where('status', '1')
                    ->where('online', User::online)
                    ->where('stripe_id', '!=', '0')
                    ->whereRaw($where)
                    ->orderByRaw($default)
                    ->paginate(20);
            }

            //  if vendor not found the return false message 
            if ($vendors->count() == 0) {
                return response(([
                    'status' => 0,
                    'message' => trans('api/user.vendor_not_found'),
                    'data' => array()
                ]), 404);
            }

            // added vendor type parameter in in vendor response according to the the ratings and payment status
            foreach ($vendors as $vendor) {
                // get the count of vendors
                $vendor_total_reviews = Review::where('vender_id',  $vendor->id)->get();
                $number_of_reviews = count($vendor_total_reviews->toArray());

                if ($vendor->payment_status == 1 && $vendor->isPro == User::proUser)
                    $vendor->vendor_type = 1;
                elseif ($vendor->payment_status == 1 &&  $vendor->isPro = User::notProUser)
                    $vendor->vendor_type = 2;
                elseif ($vendor->payment_status == 0 &&  $vendor->isPro == User::proUser)
                    $vendor->vendor_type = 3;
                else
                    $vendor->vendor_type = 4;
            }

            // return the vendor response
            return response(([
                'status' => 1,
                'message' => trans('api/user.vendor_listing'),
                'data' => new VendorCollection($vendors)
            ]), 200);
        } catch (\Exception $ex) {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/user.something_wrong');
            $this->response['data'] = array();
            return response($this->response, 500);
        }
    }

    /**
     * @param id this is the vendor id 
     * @return vendor detail
     * *funtionality:- this funtion get the id in parameter and return the user response 
     * function name : - getUserInfo
     */
    public function getUserInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {

            return response(([
                'status' => 0,
                'message' => $this->response['message'] = $validator->errors()->first(),
                'data' => array()
            ]), 400);
        }

        // find user if it is exist or not
        $user = User::where('id', $request['id'])->first();

        // if user not exist then exit and show the error
        if (!$user) {
            return response(([
                'status' => 0,
                'message' => trans('api/user.user_not_found'),
                'data' => array()
            ]), 404);
        }

        return response(([
            'status' => 1,
            'message' => trans('api/user.opration_successfull'),
            'data' => new Vendor($user)
        ]), 404);

        // !end of  getUserInfo() function
    }

    /**
     * Get reviews of perticular vendor
     * @param vendor_id
     * @return vendor review list 
     * * funtion name  :- getReviews 
     */
    public function getReviews(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required'
        ]);

        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 400);
        }
        $vendor_id = $request->vendor_id;

        // get all vendor reviews
        $vendor_data = Review::where('review_submitted_to', $vendor_id)->get();

        if (!$vendor_data->toArray()) {
            return response(([
                'status' => 0,
                'message' => trans('api/user.review_not_found'),
                'data' => array()
            ]), 404);
        }

        return response(([
            'status' => 1,
            'message' => trans('api/user.all_reviews'),
            'data' => new reviewCollection($vendor_data)
        ]), 200);
        // !end of  getReviews()
    }

    /**
     * function name :- addFavorite
     * @param  vendor_id
     * @response vendor data collection
     * *perpose :-this funtion is used to add favorite vendors
     *  
     */
    public function addFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required',
            'isFavorite' => 'required',
        ]);

        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 400);
        }
        $vendor_id = $request->vendor_id;
        $isFavorite = $request->isFavorite;

        try {
            $user = Auth::user();

            if ($user->hasRole(['vendor'])) {
                return response(([
                    'status' => 0,
                    'message' => trans('api/user.cannot_add_favorite'),
                    'data' => array()
                ]), 400);
            }
            // if vendor not exist which we want to enter as forite
            if (!User::find($vendor_id)) {
                return response(([
                    'status' => 0,
                    'message' => trans('api/user.no_vender_found_for_this_service'),
                    'data' => array()
                ]), 404);
            }

            $favorite = favorite::where(['vendor_id' => $vendor_id, "user_id" => $user->id])->first();

            // if already added then just update the is_favorite perameter in the database
            if ($favorite) {
                $favorite->is_favorite = $isFavorite;
                $favorite->save();
                return response(([
                    'status' => 1,
                    'message' => trans('api/user.update_favorite_list_success'),
                    'data' => array()
                ]), 200);
            } else {

                // if there is no data found then add the record in favorite table in db
                $favorite = favorite::create(['user_id' => $user->id, 'is_favorite' => $isFavorite, 'vendor_id' => $vendor_id]);
                if ($favorite) {
                    return response(([
                        'status' => 1,
                        'message' => trans('api/user.vendor_added_succesfully'),
                        'data' => array()
                    ]), 200);
                }
            }
        } catch (\Exception $ex) {
            $this->response['message'] = trans('api/user.something_wrong');
            return response($this->response, 500);
        }
        //  if not added then add to the favories
    }

    //  ! End of the addFavorite function

    /**
     * function name :- vandorFavoriteList
     * @param vendor_id
     * @response vendor data collection
     * *perpose :-this funtion is used to add favorite vendors
     * 
     */
    public function vandorFavoriteList()
    {
        $user = Auth::user();

        if ($user->hasRole(['vendor'])) {
            return response(([
                'status' => 0,
                'message' => trans('api/user.vendor_cannot_see_list'),
                'data' => array()
            ]), 400);
        }
        // get the favorite vendors 
        $favorite_vendor = $user->getFavoriteVendors()->where('is_favorite', '1')->paginate(10)->toArray();
        if (count($favorite_vendor['data']) == 0) {
            return response(([
                'status' => 0,
                'message' => trans('api/user.vendor_not_found'),
                'data' => array()
            ]), 404);
        }
        $vendor_ids = array_column($favorite_vendor['data'], 'vendor_id');
        $vendors_data = User::whereIn('id', $vendor_ids)->get();

        return response(([
            'status' => 1,
            'message' => trans('api/user.favorite_vendor_listing'),
            'data' => new VendorCollection($vendors_data)
        ]), 200);
    }

    /**
     * generate vendor report on the bassist of transactions 
     *
     * @param Request $request
     * @return response
     * @author Akshay akshay.aipl@gmai.com
     * 
     * in response filterBy 
     *0 :- Daily
     *1 :- Weekly
     *2 :- Monthly
     *3 :- Yearly
     */
    public function vendorReport(Request $request)
    {
        // if there is the data of month and year then today and weekly filters are not valid
        $validator = Validator::make($request->all(), [
            'filter' => 'required|integer|in:0,1,2',
        ]);
        // validation fails
        if ($validator->fails()) {
            $this->response['status'] = 0;
            $this->response['data'] = array();
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }

        $filters = array(
            'month_filter' => $request['month'] ?? '',
            'year_filter' => $request['year'] ?? '',
            'filter' => $request->filter
        );
        $user = Auth::User();
        if ($user->status == 0) {
            $this->response['message'] = trans('api/user.vender_is_inactive');
            $this->response['status'] = 0;
            $this->response['data'] = array();
            return response()->json($this->response, 403);
        }

        //-------------------------------------------------
        // if month and year not selected then send particular month of current year data
        if (!empty($filters['month_filter']) && empty($filters['year_filter'])) {
            $transaction_data = $this->get_selected_month_data_of_current_year($user->id, $filters['month_filter']);
        }
        // if month and year is selected then send particular month year data 
        else if (!empty($filters['month_filter']) && !empty($filters['year_filter'])) {
            $transaction_data = $this->get_selected_month_year_data($user->id, $filters['month_filter'], $filters['year_filter']);
        }
        // if nothing is selected then return whole year data of selected year
        else if (empty($filters['month_filter']) && !empty($filters['year_filter'])) {
            $transaction_data = $this->get_selected_year_data($user->id, $filters['year_filter']);
        }
        // get data today transaction data 
        else if ($filters['filter'] == 1) {
            $transaction_data = $this->get_today_data($user->id);
        }
        //  get current week transaction data
        else if ($filters['filter'] == 2) {
            $transaction_data = $this->get_weekly_vendor_report($user->id);
        }
        // if nothing is selected then default send the current year data
        else {
            $transaction_data = $this->send_current_year_data($user->id);
        }

        if (!count($transaction_data['data']) > 0) {

            return response(([
                'status' => 0,
                'message' => trans('api/user.report_data_not_found'),
                'data' => [],
                'filterBy' => $transaction_data['filter_by'],
            ]), 404);
        }
        return response(([
            'status' => 1,
            'filterBy' => $transaction_data['filter_by'],
            'message' => trans('api/user.vendor_report_data_found'),
            'data' => $transaction_data['data']
        ]), 200);
    }

    public function createStandardVendorAccount(Request $request)
    {
        $user = Auth::user();
        $userPaybaseId = $user->paybase_id;
        try {
            if (!$userPaybaseId) {
                return response(([
                    'status' => 0,
                    'message' => trans('api/user.paybase_user_not_created')
                ]), 200);
            }
            $body['ownerId'] = $userPaybaseId;
            $body['currencyISO'] = $request['currency_iso'];
            $userBankAccount = $this->paybaseApi($body, "https://api-json.sandbox.paybase.io/v1/accounts");
            $userBankAccountId = $userBankAccount->id;
            $user->bank_account_id = $userBankAccountId;
            $user->save();

            return response(([
                'status' => 1,
                'message' => trans('api/user.account_created')
            ]), 200);
        } catch (\Exception $ex) {
            $this->response['message'] = $this->paybaseExceptionErrorMessage($ex, trans('api/user.vendor_account_not_created'));
            return response()->json($this->response, $ex->getCode());
        }
    }
}
