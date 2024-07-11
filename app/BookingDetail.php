<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
     protected $casts = array(
        'user_id' => 'integer',
         
         
    );
    protected $table = 'bookings_details';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['booking_id', 'code', 'label', 'amount', 'start_time', 'end_time'];

    public function booking() {
        return $this->belongsTo('App\Booking');
    }

}
