<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Avatar extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'imageName' => url('assets/avatar/'.$this->image_name),
            'gender' => (int) $this->gender,
        ];
    }
}
