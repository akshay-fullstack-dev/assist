<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AgencyDocument extends Model
{
    protected $table = 'agency_document';
    protected $fillable = ['user_id', 'document1'];
    
    public function users()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
