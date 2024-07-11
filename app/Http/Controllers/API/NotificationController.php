<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Service;
use App\ServiceCategory;
use App\slot;
use App\Setting;
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
use App\Enquiry;
use App\Mail\EnquiryEmail;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{

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

    public function __construct(Service $service)
    {
        $this->service = $service;
        $this->pageLimit = config('settings.pageLimit');
        $this->response['data'] = new \stdClass();
    }

    public function getNotifications(Request $request)
    {

        $user = Auth::User();
        $notifications = Notification::select('id', 'user_id as userId', 'type_id as typeId', 'type', 'title', 'message', 'is_read as isRead', 'created_at as createdAt')->where('user_id', $user->id)->orderBy('created_at', 'DESC')->paginate(self::totalRow);
        if (!$notifications->count()) {
            $this->response['message'] = trans('api/service.no_notification');
            $this->response['data'] = array();
            $this->response['status'] = 1;
            return response()->json($this->response, 200);
        }

        $notificationData = array();
        $i = 0;
        foreach ($notifications as $notification) {
            //echo $notification['createdAt'];
            $booking = Booking::where(['id' => $notification['typeId']])->first();

            if ($booking) {
                $notificationData[$i]['notificationDateTime'] = date('Y-m-d', strtotime($notification['createdAt']));
                $notificationData[$i]['notificationType'] = $notification['type'] ? intval($notification['type']) : 0;
                $notificationData[$i]['notificationMessage'] = $notification['message'] ? $notification['message'] : '';
                $notificationData[$i]['id'] = $notification['id'];

                $notificationData[$i]['userName'] = $booking->user->firstname . ' ' . $booking->user->lastname;
                //$notificationData[$i]['userName'] = $booking->full_name;
                $notificationData[$i]['userId'] = $booking->user_id;
                $notificationData[$i]['userProfilePic'] = isset($booking->user->image) ? url('images/avatars/' . $booking->user->image) : '';
                $notificationData[$i]['serviceName'] = $booking->service_name;
                $notificationData[$i]['serviceId'] = $booking->service_id;
                $notificationData[$i]['bookingDate'] = date('Y-m-d', strtotime($booking->booking_date));
                //$notificationData[$i]['bookingSlot'] = $booking->slot->slot_from . ' - ' . $booking->slot->slot_to;
                $notificationData[$i]['bookingSlot'] = date('H:i', strtotime($booking->slot_start_from)) . ' - ' . date('H:i', strtotime($booking->slot_start_end));
                $notificationData[$i]['bookingId'] = $booking->id;
                $notificationData[$i]['bookingPrice'] = $booking->total_price;

                if ($booking->vender_id) {
                    $notificationData[$i]['vendorId'] = $booking->vender_id;
                    $notificationData[$i]['vendorProfilePic'] = isset($booking->vender->image) ? url('images/avatars/' . $booking->vender->image) : '';
                    $notificationData[$i]['vendorName'] = $booking->vender_name;
                    $notificationData[$i]['paymentPending'] = isset($booking->vender->pending_payment) ? $booking->vender->pending_payment : 0;
                }

                $reject_status = Notification::where(['user_id' => $user->id, 'type_id' => $booking->id, 'title' => Notification::bookingService])->first();

                if ($booking->status_id > Booking::venderAssigned) {

                    $notificationData[$i]['action'] = 4;
                } else if ($booking->status_id == Booking::orderPlaced && (isset($reject_status->rejected_booking) && $reject_status->rejected_booking == '1')) {

                    $notificationData[$i]['action'] = 3;
                } else {

                    $notificationData[$i]['action'] = 1;
                }
                if ($user->id == $booking->vender_id) {

                    $notificationData[$i]['action'] = 2;
                }
                if ($notification['type'] == '5') {

                    $notificationData[$i]['action'] = 13;
                }
                if ($booking->status_id == Booking::orderCanceled) {

                    $notificationData[$i]['action'] = 10;
                }
                if ($notification['type'] == '8') {

                    $getExtendHour = ExtraHour::where([['booking_id', '=', $notification['typeId']], ['status', '=', '0']])->first();
                    $getCompletedExtendHour = ExtraHour::where([['booking_id', '=', $notification['typeId']], ['status', '=', '1']])->first();
                    $getRejectedExtendHour = ExtraHour::where([['booking_id', '=', $notification['typeId']], ['status', '=', '2']])->first();
                    if ($getExtendHour) {

                        $notificationData[$i]['extentionId'] = $getExtendHour->id;
                        $notificationData[$i]['extendHours'] = $getExtendHour->extended_minutes;
                        $notificationData[$i]['action'] = 5;
                    }
                    if ($getCompletedExtendHour) {

                        $notificationData[$i]['extentionId'] = $getCompletedExtendHour->id;
                        $notificationData[$i]['extendHours'] = $getCompletedExtendHour->extended_minutes;
                        $notificationData[$i]['action'] = 6;
                    }
                    if ($getRejectedExtendHour) {

                        $notificationData[$i]['extentionId'] = $getRejectedExtendHour->id;
                        $notificationData[$i]['extendHours'] = $getRejectedExtendHour->extended_minutes;
                        $notificationData[$i]['action'] = 7;
                    }
                }
                $i++;
            }
        }
        if ($notificationData) {

            $this->response['message'] = trans('api/service.notifications_found');
            $this->response['data'] = $notificationData;
            $this->response['status'] = 1;
            return response()->json($this->response, 200);
        }
    }

    public function getFilters(Request $request)
    {
        $status = array();

        if ($request['for_bookings'] == 1) {
            $status = array(
                "1" => array('name' => "Booking placed", 'filterValue' => 1),
                "2" => array('name' => "Vender assigned", 'filterValue' => 2),
                "3" => array('name' => "Vender on the way", 'filterValue' => 3),
                "4" => array('name' => "Work in progress", 'filterValue' => 4),
            );
        } else {
            $status = array(
                "5" => array('name' => "Compleated", 'filterValue' => 5),
                "6" => array('name' => "Cancelled", 'filterValue' => 6),
                "7" => array('name' => "Refund", 'filterValue' => 7)
            );
        }
        $cats = array();
        $categories = ServiceCategory::where('status', '=', '1')->get();
        $i = 0;
        foreach ($categories as $categorie) {
            $cats[$categorie->id]['categoryName'] = $categorie['cat_name'];
            $cats[$categorie->id]['filterValue'] = $categorie['id'];
            $i++;
        }
        $array = array(array('status' => array_values($status)), array('categories' => array_values($cats)));
        $this->response['message'] = trans('api/service.all_filters');
        $this->response['data'] = $array;
        $this->response['status'] = 1;
        return response()->json($this->response, 200);
    }

    /**
     * send email for enquiries
     */
    public function enquiry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required'],
            'subject' => ['required'],
            'message' => ['required'],
            'fullname' => ['required']
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }
        $admin_data = Setting::first();

        $enquiry = new Enquiry();
        $enquiry->email = $request['email'];
        $enquiry->fullname = $request['fullname'];
        $enquiry->subject = $request['subject'];
        $enquiry->message = $request['message'];
        $enquiry->booking_id = $request['booking_id'];
        $enquiry->status = 'pending';

        if ($enquiry->save()) {
            $mail['email'] = $request['email'];
            $mail['subject'] = $request['subject'];
            $mail['message'] = $request['message'] . ' Order id :'. $request['booking_id'];
            Mail::to($admin_data->email)->send(new EnquiryEmail($mail));
            $this->response['status'] = 1;
            $this->response['message'] = trans('api/message.enquiry_mail_sent_successfully');
            return response()->json($this->response, 200);
        }
        else {
            $this->response['status'] = 0;
            $this->response['message'] = trans('api/message.something_wrong');
            return response()->json($this->response, 500);
        }
    }
}
