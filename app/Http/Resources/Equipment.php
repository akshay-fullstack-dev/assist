<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Equipment extends Resource
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
            'name' => $this->name,
            'image' => isset($this->image) ? url('assets/equipments/'. $this->image) :"",
            'price' => $this->price ,
            'serviceId' => $this->service_id 
        ];
    }
}
