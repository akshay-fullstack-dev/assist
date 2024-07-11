<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceSelectedFrequency extends Model
{
    protected $guarded = [];
    public function services()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
    public function service_frequencies()
    {
        return $this->belongsTo(ServiceFrequency::class, 'frequency_id');
    }
}
