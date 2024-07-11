<?php

namespace App\Helpers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationHelper
{
    public function getUserNotifications()
    {
        $notifications = Notification::where('is_read', 0)->get();
        return $notifications;
    }

}

?>
