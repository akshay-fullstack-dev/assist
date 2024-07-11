<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'status';
    
    protected $fillable = ['status_type', 'label'];
    
    public function booking()
    {
        return $this->hasMany('App\Booking', 'status_id', 'id');
    }
    
    public function bookingStatusHistory()
    {
        return $this->hasMany('App\BookingStatusHistory');
    }
    public function notification()
    {
        return $this->hasMany('App\Notification');
    }
}
