<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeviceDetails extends Model
{
    const androidPlatform = 1;
    const iosPlatform = 0;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'device_details';
    protected $fillable = ['access_token_id', 'device_token', 'device_id', 'build_version', 'platform', 'build','user_id', 'user_type'];

    public function scopeAndroidTokens($query)
    {
        return $query->where('platform', 1)->whereNotNull('device_token');
    }

    public function scopeIosToken($query)
    {
        return $query->where('platform', 0)->whereNotNull('device_token');
    }

}
