<?php

namespace App\Models;

use App\Services\PushNotification;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = ['*'];
    public $timestamps = true;

    public function getUser()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
    
    public function status()
    {
        return $this->belongsTo('App\Status', 'type', 'id');
    }

    public static function createNotification($userId = NULL, $typeId, $type, $title, $message)
    {
        
        $user = ($userId) ?  User::find($userId) : Auth::User();
        
        $notification = new Notification();
        $pushNotification = new PushNotification();

        $notification->type_id = $typeId;
        $notification->type = $type;
        $notification->title = $title;
        $notification->message = $message;
        $notification->is_read = 0;
        $notification->save();



        $notificationData['data']['"id"'] = $notification->id;
        $notificationData['data']['"user_id"'] = '"' . $user->id . '"';
        $notificationData['data']['"type_id"'] = $notification->type_id ? $notification->type_id : 0;
        $notificationData['data']['"type"'] = '"' . $$notification->type . '"';
        $notificationData['data']['"title"'] = '"' . $notification->title . '"';
        $notificationData['data']['"message"'] = '"' . $notification->message . '"';
        $notificationData['data']['"created_at"'] = '"' . $notification->updated_at . '"';
        $notificationData['data']['"updated_at"'] = '"' . $notification->updated_at . '"';
        $notificationData['data']['"notification_type"'] = 0;
        $notificationData['message'] = $message;
        $notificationData['title'] = $title;

        $pushNotification->sendNotification($notificationData, $user->id);

        return $notification;
    }

}
