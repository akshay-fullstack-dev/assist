<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class favorite extends Model
{
    //
    public $timestamps = false;
    protected $guarded = [];

    public function favVendor() {
        return $this->belongsTo('App/User', 'user_id', 'id');
    }
}
