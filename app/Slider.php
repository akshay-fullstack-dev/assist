<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $table = 'banners';
    protected $fillable = ['name', 'image'];
}
