<?php

namespace App\Http\Controllers\Admin;

use Auth;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\User;
use App\Notification as not;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\Admin\SendAdminMessage;

class NotificationController extends Controller {

    public function getNotifications($type = null) {
        if ($type == 'all') {
            $notifications = Notification::with('getUser')->orderBy('id', 'DESC')->with('status')->paginate(10);
        } else {
            $notifications = Notification::where('is_read', 0)->with('getUser')->orderBy('id', 'DESC')->where('for_admin', 1)->with('status')->get();
            foreach ($notifications as $notification) {
                $notification->is_read = 1;
                $notification->save();
            }
        }
        return view('admin/notifications', compact('notifications', 'type'));
    }

    public function createNotification() {

        //return view('admin/notifications', compact('notifications', 'type'));
        $users = User::whereHas('roles', function($q) {
                    $q->where('name', 'Vendor');
                })->pluck('firstname', 'id');
        return view('admin/createNotification', compact('users'));
    }

    public function sendNotification(Request $request) {

        $validator = Validator::make($request->all(), [
                    'user_id' => ['required'],
                    'message' => ['required'],
        ]);
        if ($validator->fails()) {
            $this->response['message'] = $validator->errors()->first();
            return response()->json($this->response, 401);
        }

        $user_id = $request->user_id;
        $user = User::findOrfail($user_id);
        $message = $request->message;
        Not::createNotification(0, Not::emailMessageFromAdmin, Not::emailMessageTitle, $message, $user_id);
        
        $mail['email'] = 'admin@mailinator.com';
        $mail['subject'] = 'New email from Assist admin';
        $mail['message'] = $request->message;
        Mail::to($user->email)->send(new SendAdminMessage($mail));
        return redirect('admin/createnotification')->with('success_message', trans('admin/user.message_sent_successfully'));
        
    }

}
