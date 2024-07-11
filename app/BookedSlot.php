<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookedSlot extends Model
{
    protected $table = 'booked_slot';
    protected $fillable = ['booking_id', 'vender_id', 'slot_id', 'status_id', 'booking_date'];
    
    
    public function vender() {
        return $this->belongsTo('App\User', 'vender_id', 'id');
    }
    public function booking() {
        return $this->belongsTo('App\Booking', 'booking_id', 'id');
    }
}
