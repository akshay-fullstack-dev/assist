<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Service;
use App\slot;
use App\ExtraHour;
use App\User;
use App\BookingStatusHistory;
use App\venderSlot;
use App\UserAddresses;
use App\Booking;
use App\BookingDetail;
use App\Notification;
use App\Services\PushNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Booking as BookingResource;
use App\Http\Resources\venderBookingList;
use App\Http\Resources\venderBookingListCollection;
use App\Http\Resources\BookingCollection;
use App\Http\Resources\Invoice as InvoiceResource;
use App\Http\Resources\NotificationCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Http\Resources\OrderCollection;

class HistoryController extends Controller {

    protected $response = [
        'status' => 0,
        'message' => '',
    ];

    const totalRow = 20;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $service;
    protected $pageLimit;

    public function __construct(Service $service) {
        $this->service = $service;
        $this->pageLimit = config('settings.pageLimit');
        $this->response['data'] = new \stdClass();
    }

    public function getHistory(Request $request) {

        $user = Auth::User();
        $status_id = array(Booking::orderRefund, Booking::orderCanceled, Booking::orderCompleted);
        $service_ids = array();
        if (isset($request['status']) && !empty($request['status'])) {
            $status_id = $request['status'];
        }
        if (isset($request['category_ids']) && !empty($request['category_ids'])) {

            $category_ids = $request['category_ids'];
            $service_ids = Service::select('id')->whereIn('cat_id', $category_ids)->get()->toArray();

            $service_ids = array_column($service_ids, 'id');
        }
        $all_orders = '';
        if ($user->hasRole('vendor')) {
            $query = Booking::where('vender_id', '=', $user->id)
                    ->whereIn('status_id', $status_id);
            if (!empty($service_ids)) {
                $query->whereIn('service_id', $service_ids);
            }
            $all_orders = $query->orderBy('id', 'DESC')->paginate(self::totalRow);
        } else {
            $query = Booking::where('user_id', '=', $user->id)
                    ->whereIn('status_id', $status_id);
            if (!empty($service_ids)) {
                $query->whereIn('service_id', $service_ids);
            }
            $all_orders = $query->orderBy('id', 'DESC')->paginate(self::totalRow);
        }
        return (new OrderCollection($all_orders))->additional([
                    'status' => 1,
                    'message' => trans('api/user.all_orders')
        ]);
    }

}
