<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Services\PushNotification;
use App\User;
use App\Booking;


class Notification extends Model
{

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    const bookingService = "New Job";
    const acceptBookingTitle = "Job Acception";
    const cancelBookingTitle = "Job Cancel";
    const rescheduleBookingTitle = "Reschedule Job";
    const priorBookingTitle = "priorBooking";
    const venderOnTheWayBooking = "Vendor on the way.";
    const venderStartedJob = "Vender Started  the Job.";
    const extendJobTime = "Job Extension Time";
    const orderCompleted = "Job Done";
    const chatMessage = "New Message";
    const emailMessageTitle = "New Email message from admin";
    const bookinOnHold = "Booking on hold";
    const booking = '1';
    const acceptBooking = '3';
    const priorBooking = '2';
    const chatNotification = '4';
    const rescheduleBooking = '5';
    const paymentNotification = '6';
    const extendTimeNotification = '8';
    const refundNotification = '9';
    const cancelbooking = '10';
    const emailMessageFromAdmin = '12';
    const bookingOnHoldStatus = '14';
    const booking_reminder = '15';

    public static function createNotification($itemId, $itemType, $notiTitle, $notiMessage, $userId = null, $foradmin = null)
    {
        $notificationData = array();

        $user = ($userId) ? User::find($userId) : Auth::User();
        $booking = '';
        if ($itemType == '1') {
            $booking = Booking::where(['id' => $itemId])->first();
        }
        $pushNotification = new PushNotification();
        $notification = new Notification;
        $notification->user_id = $user->id;
        $notification->for_admin = $foradmin ? 1 : 0;
        $notification->type_id = $itemId ? $itemId : 0;
        $notification->type = $itemType;
        $notification->title = $notiTitle;
        $notification->message = $notiMessage;
        $notification->save();
        $filename = 'api_datalogger_' . date('d-m-y') . '.log';
        //\File::append( storage_path('logs' . DIRECTORY_SEPARATOR . $filename), 'Notification got here start here'.$notification.'notification end here' . "\n" . str_repeat("=", 20) . "\n\n");

        $notificationData["data"]["item_type"] = $notification->item_type;
        $notificationData["data"]["item_id"] = $itemId ? $itemId : 0;
        $notificationData["data"]["id"] = $notification->id;
        $notificationData["data"]["noti_title"] = $notiTitle;
        if (isset($booking->id)) {
            $notificationData["data"]["bookingId"] = $booking->id;
            $notificationData["data"]["bookingDate"] = $booking->booking_date;
            $notificationData["data"]["userName"] = $booking->full_name;
            $notificationData["data"]["serviceName"] = $booking->service_name;
            $notificationData["data"]["bookingTimeSlot"] = $booking->slot_start_from . '-' . $booking->slot_start_to;
            $notificationData["data"]["userProfileImage"] = ($booking->user->image) ? url('images/avatars/' . $booking->user->image) : "";
        }
        $notificationData["data"]["updated_at"] = $notification->updated_at;
        $notificationData["data"]["noti_message"] = $notiMessage;
        $notificationData["data"]["created_at"] = $notification->updated_at;
        $notificationData["data"]["user_id"] = $notification->user_id;
        $notificationData["data"]["created_at"] = $notification->created_at;
        $notificationData["data"]["notification_type"] = 0;

        if ($itemType == '4') {
            $notificationData["data"]["notification_type"] = 1;
        }
        $notificationData["message"] = $notiMessage;
        $notificationData["title"] = $notiTitle;

        $status = $pushNotification->sendNotification($notificationData, $user->id);
        return $notification;
    }
}
