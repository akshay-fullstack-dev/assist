<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class CancelledBooking extends Model{

    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'cancelled_bookings';
    
    protected $fillable = ['vender_id', 'booking_id'];

    public function vender() {
        return $this->belongsTo('App\User', 'id', 'vender_id');
    }
}
