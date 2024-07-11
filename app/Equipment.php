<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipments';
    protected $fillable = ['name', 'image', 'price', 'service_id'];
    
    public function service()
    {
        return $this->belongsTo('App\Service', 'id', 'service_id');
    }
    
     public function bookedEquipment()
    {
        return $this->belongsTo('App\BookedEquipment', 'equipment_id');
    }
}
