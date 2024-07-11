<?php

namespace App\Http\Resources;

use App\AvatarImage;
use Illuminate\Http\Resources\Json\Resource;
use App\UserAddresses;
use App\Booking;
use App\Http\Resources\AddressCollection;
use App\Http\Resources\AvatarCollection;
use App\Http\Resources\VenderServiceCollection;
use Illuminate\Support\Facades\Auth;

class User extends Resource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private $token;

    public function __construct($resource, $token = "")
    {
        parent::__construct($resource);
        $this->token = $token;
    }

    public function toArray($request)
    {

        $data = [];
        if ($this->hasRole(['vendor'])) {
            $data = [
                'service' => VenderService::collection($this->venderServices),
                'status' => (int) $this->status,
                'roleId' => 2,
                "agencyName" => ($this->agency_name) ? $this->agency_name : "",
                'profileImage' => ($this->image) ? url('images/avatars/' . $this->image) : "",
                'isOnline' => (int) $this->online,
                'venderDoc' => ($this->vender_doc) ? url('images/avatars/' . $this->vender_doc) : "",
                'paybaseId' => ($this->paybase_id) ? $this->paybase_id : ""
                
            ];
        }
        if ($this->hasRole(['user'])) {

            $avatar_images = AvatarImage::get();
            $selected_image = AvatarImage::find($this->avtaar_image);

            $bookingCount = Booking::where([['user_id', '=', $this->id], ['status_id', '!=', Booking::orderCanceled]])->get()->count();
            $data = [
                'discountType' => 3,
                'roleId' => 1,
                'bookingCount' => $bookingCount,
                'selectedAvtaarImage' => ($selected_image == "") ? "" : url('assets/avatar/' . $selected_image->image_name),
                'discountValue' => (int) $this->online,
                'bookedServicesCount' => Booking::where(array('status_id' => Booking::orderCompleted, 'user_id' => $this->id))->get()->count(),
                'avtaarImages' => Avatar::collection($avatar_images),
                'paybaseId' => ($this->paybase_id) ? $this->paybase_id : "",
                'cardId' => ($this->card_id) ? $this->card_id : "",
                'cardNumberSuffix' => ($this->card_number_suffix) ? $this->card_number_suffix : "",
            ];
        }
        return array_merge($data, [
            'id' => $this->id,
            'fbId' => $this->fb_id ? $this->fb_id : '',
            'username' => $this->firstname ? $this->firstname : '',
            'email' => $this->email ? $this->email : '',
            'myRefferalCode' => $this->refferal ? $this->refferal : '',
            'refferedBy' => $this->reffer_code ? $this->reffer_code : '',
            'phoneNumber' => $this->phone_number ? $this->phone_number : '',
            'selectedAddress' => ($this->selected_address != null && ($this->selected_address != '0')) ? $this->selected_address : 0,
            'gender' => intval($this->gender ? $this->gender : '0'),
            'address' => Address::collection($this->userAddress),
            'bio' =>  $this->bio ? $this->bio : '',
            'isActive' => $this->status ? true : false,
            'loginType' => (empty($this->fb_id)) ? 0 : 1,
            'isVerified' => ($this->is_verified == 1) ? true : false,
            'token' => $this->token,
        ]);
    }
}
