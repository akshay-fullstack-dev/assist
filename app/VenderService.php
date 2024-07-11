<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class VenderService extends Model
{
    //use SoftDeletes;
    protected $table = 'vender_services';
    protected $fillable = ['vender_id', 'service_id', 'price', 'cat_id'];
    
    public function users()
    {
        return $this->belongsTo('App\Users', 'id', 'vendor_id');
    }
    public function service()
    {
        return $this->belongsTo('App\Service', 'service_id', 'id');
    }

}
