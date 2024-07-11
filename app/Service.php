<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    const active_serivce = "1";
    protected $table = 'services';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'description', 'price_type', 'status', 'image', 'parent_id', 'cat_id', 'service_question', 'option_1_price', 'option_2_price', 'option_1', 'option_2'];

    public function scopeActive($query)
    {
        return $query->whereStatus('1');
    }

    public function schedule()
    {
        return $this->hasMany('App\Schedule');
    }

    public function booking()
    {
        return $this->hasMany('App\Booking');
    }

    public function vendorService()
    {
        return $this->belongsTo('App\Users', 'id', 'service_id');
    }
    public function ServiceCategory()
    {
        return $this->belongsTo('App\ServiceCategory', 'cat_id', 'id');
    }

    public function equipment()
    {
        return $this->hasMany('App\Equipment');
    }

    public function coupons()
    {
        return $this->belongsToMany('App\Coupon');
    }

    public function selected_service_frequencies()
    {
        return $this->hasMany(ServiceSelectedFrequency::class, 'service_id');
    }
    public function service_additional_questions()
    {
        return $this->hasMany(ServiceAdditionalOptions::class, 'service_id');
    }
}
