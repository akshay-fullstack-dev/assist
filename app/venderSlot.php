<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class venderSlot extends Model
{
    protected $table = 'vender_slots';
    protected $fillable = ['vender_id', 'slot_id'];
    
    
    public function slot() {
        return $this->belongsTo('App\slot', 'slot_id', 'id');
    }
    
    public function vender() {
        return $this->belongsTo('App\User', 'vender_id', 'id');
    }
}
