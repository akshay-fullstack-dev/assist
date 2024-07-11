<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'bookings';

    const orderPlaced = 1;
    const venderAssigned = 2;
    const venderOnTheWay = 3;
    const orderInProgres = 4;
    const orderCompleted = 5;
    const orderCanceled = 6;
    const orderRefund = 7;
    const extentionPending = 8;
    const extentionCompleted = 9;
    const extentionRejected = 10;
    const venderArived = 11;
    const rescheduled = 13;
    const onHold = 14;
    const bookingNotInHold = '0';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'service_id', 'booking_date', 'full_name', 'email', 'phone', 'address', 'status_id', 'vender_name', 'vender_id', 'slot_id', 'selected_hours', 'slot_start_from', 'booking_note', 'slot_start_end', 'price', 'total_price', 'gender_preference', 'notes', 'booking_type', 'accepted_at', 'payment_type', 'service_name', 'feedback', 'cancelled_by', 'reason_of_cancellation', 'job_description', 'additional_equipments', 'is_onhold', 'slots_time'];

    // filter constant
    const filterByDay = 1;
    const filterByWeek = 2;
    const filterByMonth = 3;
    const filterByYear = 4;


    public function scopeActive($query)
    {
        return $query->whereStatus('1');
    }

    public function scopeUserBy($query, $id)
    {
        return $query->whereUserId($id);
    }
    public function scopeVenderBy($query, $id)
    {
        return $query->whereVenderId($id);
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    public function bookingDetail()
    {
        return $this->hasMany('App\BookingDetail');
    }

    public function couponHistory()
    {
        return $this->hasOne('App\CouponHistory', 'booking_id', 'id');
    }

    public function ExtraHour()
    {
        return $this->hasMany('App\ExtraHour');
    }

    public function review()
    {
        return $this->hasMany('App\Review');
    }

    public function bookingStatusHistory()
    {
        return $this->hasMany('App\BookingStatusHistory');
    }

    public function status()
    {
        return $this->belongsTo('App\Status', 'status_id', 'id');
    }

    public function vender()
    {
        return $this->belongsTo('App\User', 'vender_id', 'id');
    }

    public function slot()
    {
        return $this->belongsTo('App\slot');
    }

    public function bookedEquipment()
    {
        return $this->hasMany('App\BookedEquipment');
    }
}
