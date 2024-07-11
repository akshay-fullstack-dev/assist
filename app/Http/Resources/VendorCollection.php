<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VendorCollection extends ResourceCollection
{ 
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public $collects = 'App\Http\Resources\Vendor';
    public function toArray($request)
    {
       
        // return parent::toArray($request);
        return $this->collection;
    }
}
