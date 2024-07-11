<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\AvatarImage;

class Chat extends Resource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        
         
        
        $avtar_image = $this->sender->image ? url('images/avatars/' . $this->sender->image) : '';
        
        if (isset($this->sender->avtaar_image)) {
            $avtar_image = AvatarImage::select('image_name')->where('id', $this->sender->avtaar_image)->first();
            $avtar_image = url('assets/avatar/' . $avtar_image->image_name);
        } 

        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'body' => $this->body,
            'senderName' => $this->sender->firstname . ' ' . $this->sender->lastname,
            'image' => $avtar_image ? $avtar_image : '',
            //'userImage' => $this->user->image ? url('images/avatars/' . $this->user->image) : '',
            //'venderImage' => $this->vender->image ? url('images/avatars/' . $this->vender->image) : '',
            'createdAt' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }

}
