<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Ultraware\Roles\Traits\HasRoleAndPermission;
use Ultraware\Roles\Contracts\HasRoleAndPermission as HasRoleAndPermissionContract;

use App\Notifications\ResetPasswordUser as ResetPasswordNotification;

class User extends Authenticatable implements HasRoleAndPermissionContract
{

    use HasApiTokens, Notifiable, HasRoleAndPermission;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    const inActive = '0';
    const active = '1';
    const pending = '2';
    const rejected = '3';

    // otp verified by number or not
    const not_verified = '0';
    const verified = '1';

    const rejectAgency = 'rejected';
    const inactiveAgency = 'inactive';
    const activeAgency = 'active';

    const activeUser = '1';
    const minRatingsForPro = '200';

    const proUser = '1';
    const notProUser = '0';

    const isPaid = '1';
    const notPaid = '0';
    const online = "1";
    const offline = "0";

    const notSyncWithSandgrid = '0';
    const SyncedWithSandgrid = '1';


    protected $fillable = ['fb_id', 'firstname', 'lastname', 'email', 'password', 'image', 'vender_doc', 'bio', 'credit', 'is_verified', 'online', 'status', 'rejection_reason', 'user_type', 'phone_number', 'gender', 'pending_payment', 'refferal', 'reffer_code', 'agency_id', 'agency_name', 'otp', 'isPro', 'payment_status'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function scopeActive($query)
    {
        return $query->whereStatus('1');
    }

    public function scopeOnline($query)
    {
        return $query->whereOnline('1');
    }

    public function chat()
    {
        return $this->hasMany('App\Chat', 'id', 'user_id');
    }

    public function booking()
    {
        return $this->hasMany('App\Booking');
    }

    public function vendorBooking()
    {

        return $this->hasMany('App\Booking', 'vender_id', 'id');
    }

    public function favorite()
    {
        return $this->hasMany('App\favorite');
    }

    public function transaction()
    {
        return $this->hasMany('App\Transaction');
    }
    public function couponHistory()
    {
        return $this->hasMany('App\CouponHistory');
    }

    public function venderServices()
    {
        return $this->hasMany('App\VenderService', 'vender_id', 'id');
    }

    public function venderSlots()
    {
        return $this->belongsToMany('App\slot', 'vender_slots', 'vender_id', 'slot_id');
    }
    public function userAddress()
    {
        return $this->hasMany('App\UserAddresses', 'user_id', 'id');
    }
    public function selectedAddress()
    {
        return $this->hasOne('App\UserAddresses', 'id', 'selected_address');
    }

    public function agencyDocument()
    {
        return $this->hasMany('App\AgencyDocument', 'user_id', 'id');
    }

    public function generalHistory()
    {
        return $this->hasMany('App\GeneralHistory', 'user_id', 'id');
    }

    public function package()
    {
        return $this->hasMany('App\UserPackage');
    }

    public function userPhoneOtp()
    {
        return $this->hasOne('App\phoneOtp', 'phone_no', 'phone_number');
    }

    public function roles()
    {
        return $this->belongsToMany('App\Models\Role');
    }

    public function hasNotifications()
    {
        return $this->hasMany('App\Notification');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    //   public function role() {
    //        return $this->belongsToMany('App\Role')->withTimestamps();
    //    }

    public function scopeActiveUser($query)
    {
        return $query->where('status', $this->activeUser);
    }
    public function getFavoriteVendors()
    {
        return $this->hasMany('App\favorite');
    }
}
