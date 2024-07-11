<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GeneralHistory extends Model
{
    protected $table = 'general_history';
    protected $fillable = ['user_id', 'type', 'message'];
    
    
    public function users()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
