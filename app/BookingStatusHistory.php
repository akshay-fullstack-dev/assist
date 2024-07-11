<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingStatusHistory extends Model
{
    protected $table = 'booking_status_history';
    
    protected $fillable = ['booking_id', 'status_id', 'user_type'];
    
    public function bookings()
    {
        return $this->belongsTo('App\Booking');
    }
    
    public function status()
    {
        return $this->belongsTo('App\Status');
    }
}
