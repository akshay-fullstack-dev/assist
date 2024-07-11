<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Category extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //$serviceCollection = new ServiceCollection($this->Services);
        if ($this->Services->count() > 0) {
            return [
                'id' => $this->id,
                'serviceName' => $this->cat_name,
                'image' => url('assets/category/' . $this->image),
                'subServices' => Service::collection($this->Services),
            ];
        }
    }
}
