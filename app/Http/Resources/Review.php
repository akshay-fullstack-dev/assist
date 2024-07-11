<?php

namespace App\Http\Resources;

use App\User;
use App\AvatarImage;
use Illuminate\Http\Resources\Json\Resource;

class Review extends Resource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {


        $submittedBy = '';
        $user_id = '';
        $vender_id = '';
        if ($this->user_id == $this->review_submitted_by) {

            $submittedBy = $this->user->firstname . ' ' . $this->user->lastname;
            $user_id = $this->user_id;
            $vender_id = $this->vender_id;
            $image = isset($this->submitter_image) ? url('assets/avatar/' . $this->submitter_image) : "";
        } else {
            $user_id = $this->user_id;
            $vender_id = $this->vender_id;
            $submittedBy = $this->vender->firstname . ' ' . $this->vender->lastname;
            $image = isset($this->submitter_image) ? url('uploads/user/' . $this->submitter_image) : "";
        }
        
        return [
            'id' => $this->id,
            'userId' => $user_id,
            'venderId' => $vender_id,
            'reviewSubmittedBy' => $submittedBy,
            'message' => ($this->feedback_message)? $this->feedback_message :"",
            'rating' => (int) $this->rating,
            'like' => (int) $this->is_like,
            'image' => $image
        ];
    }
}
