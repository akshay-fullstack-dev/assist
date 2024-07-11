<?php

namespace App\Http\Resources;

use App\AvatarImage;
use Illuminate\Http\Resources\Json\Resource;
use App\Http\Resources\Address;
use App\Http\Resources\BookingHistory;
use App\Http\Resources\BookingHistoryCollection;
use App\Http\Resources\ExtraHourHistory;
use App\Http\Resources\ExtraHourCollection;
use App\Http\Resources\ReviewCollection;
use App\Http\Resources\Review as ReviewResource;
use Illuminate\Support\Facades\Auth;
use App\Review;
use App\Booking as Book;

//use App\ExtraHour;


class Booking extends Resource
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


        $user = Auth::User();
        $data = [];
        $status_id = array(Book::orderPlaced, Book::venderAssigned, Book::venderOnTheWay, Book::orderInProgres, Book::venderArived, Book::orderCompleted);
        if ($user->hasRole(['vendor'])) {
            $hours = 0;
            $extentionAcceptedAt = '';
            $extraHours = ExtraHour::collection($this->ExtraHour);
            foreach ($extraHours as $extraHour) {
                $hours = $extraHour->extended_minutes ? $extraHour->extended_minutes : 0;
                $extentionAcceptedAt = $extraHour->extention_accepted_at ? $extraHour->extention_accepted_at : '';
            }

            $data = [
                'status' => BookingHistory::collection($this->bookingStatusHistory)->whereIn('status_id', $status_id)->unique('status')->values(),
                'jobDescription' => $this->notes ? $this->notes : '',
                'currentStatus' => $this->status_id,
                'extendedHours' => $hours,
                'extentionAcceptedAt' => $extentionAcceptedAt,
                'bookedSlots' => $this->slots_time,
            ];
        } else {

            $data = [
                'serviceId' => $this->service_id,
                'serviceImage' => isset($this->service->image) ? url('/assets/services') . '/' . $this->service->image : '',
                'serviceName' => $this->service_name,
                'venderId' => $this->vender_id ? $this->vender_id : 0,
                'venderName' => $this->vender_name ? $this->vender_name : '',
                'status' => $this->status->id,
                'createdAt' => $this->created_at->format('Y-m-d H:i:s'),
                'slot' => $this->slot_start_from . '-' . $this->slot_start_end,
            ];
        }

        $userReview = Review::where([['booking_id', '=', $this->id], ['review_submitted_by', '=', $this->user_id]])->first();
        $venderReview = Review::where([['booking_id', '=', $this->id], ['review_submitted_by', '=', $this->vender_id]])->first();
        $avatar_id = isset($this->user->avtaar_image) ? $this->user->avtaar_image : "";
        $getAvatarImage = AvatarImage::where('id', $avatar_id)->first();
        return [
            'bookingDetails' => array_merge([
                'id' => $this->id,
                'vendorImage' => isset($this->vender->image) ? url('public/images/' . $this->vender->image) : '',
                'scheduleDate' => $this->booking_date,
                'equipments' => $this->additional_equipments ? $this->additional_equipments : '',
                'bookingNote' => $this->booking_note ? $this->booking_note : '',
                'additionalEquipments' => $this->bookedEquipment ? BookedEquipment::collection($this->bookedEquipment) : '',
            ], $data),
            'paymentDetails' => [
                'serviceCost' => (float) $this->total_price ? $this->total_price : 0,
                'discountAmount' =>  isset($this->couponHistory->discount) ? $this->couponHistory->discount  : 0,
                'totalHours' => (float) $this->selected_hours ? $this->selected_hours : 0,
                'totalPrice' => (float) $this->total_price ? $this->total_price : 0,

            ],
            'customerDetails' => [
                'name' => $this->full_name ? $this->full_name : '',
                'phone' => $this->user->phone_number ? $this->user->phone_number : '',
                'profileImage' => (isset($getAvatarImage->image_name)) ? asset('assets/avatar') . "/" . $getAvatarImage->image_name : '',
                'address' => ($this->user->selectedAddress) ? Address::make($this->user->selectedAddress) : "",
            ],

            'userReview' => $userReview ? new ReviewResource(Review::where([['booking_id', '=', $this->id], ['review_submitted_by', '=', $this->user_id]])->first()) : new \stdClass(),
            'venderReview' => $venderReview ? new ReviewResource(Review::where([['booking_id', '=', $this->id], ['review_submitted_by', '=', $this->vender_id]])->first()) : new \stdClass()
        ];
    }
}
