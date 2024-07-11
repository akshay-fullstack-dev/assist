<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AvatarImage extends Model
{
    public $timestamps = false;
    protected $table = 'avatar_images';
    protected $fillable = ['image_name'];

    
}
