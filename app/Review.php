<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    const reviews_for_pro = 5;

    
    const average_rating_for_pro = 4.8;
    protected $table = 'review';
    protected $fillable = ['user_id', 'vender_id', 'booking_id', 'rating', 'is_like', 'review_submitted_by', 'review_submitted_to', 'review_type', 'feedback_message', 'submitter_image'];


    public function slot()
    {
        return $this->belongsTo('App\slot', 'slot_id', 'id');
    }
    public function booking()
    {
        return $this->belongsTo('App\slot', 'booking_id', 'id');
    }

    public function vender()
    {
        return $this->belongsTo('App\User', 'vender_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
