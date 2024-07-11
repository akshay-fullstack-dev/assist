<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class phoneOtp extends Model
{
    protected $fillable = ['phone_no', 'otp'];
}
