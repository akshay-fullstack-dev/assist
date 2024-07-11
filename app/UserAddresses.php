<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAddresses extends Model
{
    protected $table = 'user_addresses';
    protected $fillable = ['user_id', 'place_id', 'name', 'phone', 'gender', 'latitude', 'longitude', 'city', 'house_no', 'landmark', 'country', 'pincode', 'full_address', 'address_type'];
    
    public function users()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
