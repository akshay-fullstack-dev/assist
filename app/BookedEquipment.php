<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookedEquipment extends Model
{
    protected $table = 'booked_equipments';
    protected $fillable = ['booking_id', 'equipment_id', 'price', 'quantity', 'equipment_name'];
    public $timestamps = false;
    
    public function booking() {
        return $this->belongsTo('App\Booking', 'id', 'booking_id');
    }
    public function equipment() {
        return $this->belongsTo('App\Equipment', 'id', 'equipment_id');
    }
}
