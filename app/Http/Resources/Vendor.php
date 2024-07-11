<?php

namespace App\Http\Resources;

use App\favorite;
use App\Http\Resources\VenderService;
use Illuminate\Http\Resources\Json\Resource;
use \App\VenderService as Vservice;
use \App\Review;
use \App\UserPackage;
use Illuminate\Support\Facades\Auth;
use \App\User;

class Vendor extends Resource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [];
        $user = Auth::User();
        $is_fav = favorite::where([['user_id', '=', $user->id], ['vendor_id', '=', $this->id], ['is_favorite', '=', '1']])->first();

        $date = date('Y-m-d');
        $package = UserPackage::where([['user_id', '=', $this->id], ['expiry_date', '>=', $date]])->first();
        $vendor_total_reviews = Review::where('review_submitted_to',  $this->id)->get();
        $number_of_reviews = (count($vendor_total_reviews->toArray()) != 0) ? count($vendor_total_reviews->toArray()) : 1;
        if (count($vendor_total_reviews->toArray()) != 0) {
            $average_rating = (float) number_format(($this->rating / $number_of_reviews), 1, '.', '');
        } else {
            $average_rating = 0.0;
        }

        $vender_type = 4;
        if ($this->isPro == User::proUser && $this->payment_status == User::isPaid) {
            $vender_type = 1;
        }
        if ($this->isPro == User::notProUser && $this->payment_status == User::isPaid) {
            $vender_type = 2;
        }
        if ($this->isPro == User::proUser && $this->payment_status == User::notPaid) {
            $vender_type = 3;
        }
        if ($this->isPro == User::notProUser && $this->payment_status == User::notPaid) {
            $vender_type = 4;
        }
        return $data = [
            'id' => $this->id,
            'fbId' => $this->fb_id ? $this->fb_id : '',
            'username' => $this->firstname ? $this->firstname : '',
            'email' => $this->email ? $this->email : '',
            'myRefferalCode' => $this->refferal ? $this->refferal : '',
            'refferedBy' => $this->reffer_code ? $this->reffer_code : '',
            'isFav' => isset($is_fav->id) ? 1 : 0,
            'phoneNumber' => $this->phone_number ? $this->phone_number : '',
            'selectedAddress' => ($this->selected_address != null && ($this->selected_address != '0')) ? $this->selected_address : 0,
            'gender' => intval($this->gender ? $this->gender : '0'),
            'address' => Address::collection($this->userAddress),
            'isActive' => $this->status ? true : false,
            'loginType' => (empty($this->fb_id)) ? 0 : 1,
            'isVerified' => ($this->is_verified == 1) ? true : false,
            'paymentStatus' => (int) $this->payment_status,
            'bio' => $this->bio ? $this->bio : '',
            'rating' => $average_rating,
            'service' => VenderService::collection($this->venderServices),
            'status' => (int) $this->status,
            "agencyName" => ($this->agency_name) ? $this->agency_name : "",
            'profileImage' => ($this->image) ? url('images/avatars/' . $this->image) : "",
            'isOnline' => (int) $this->online,
            'venderDoc' => ($this->vender_doc) ? url('images/avatars/' . $this->vender_doc) : "",
            'reviewsCount' => count(Review::where('review_submitted_to', $this->id)->get()->toArray()),
            'vendorType' => $vender_type,
            'bank_account_id' => $this->bank_account_id ? $this->bank_account_id : ""
        ];
    }
}
