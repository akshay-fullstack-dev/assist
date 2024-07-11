<?php

namespace App\Http\Resources;

use App\ServiceSelectedFrequency;
use Illuminate\Http\Resources\Json\Resource;

class ServiceFrequenciesResource extends Resource
{
    // public function __construct($collection, $service_id)
    // {
    //     echo $service_id;die;
    //     $this->service_id = $service_id;
    //     parent::__construct($collection);
    // }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $selected_service = ServiceSelectedFrequency::whereService_id($this->service_id)->whereFrequency_id($this->id)->first();
        return [
            'frequency_id' => $this->id,
            'frequency_name' => $this->frequency_name,
            'frequency_day' => $this->frequency_day,
            'frequency_price' => $selected_service ? $selected_service->service_price : 0
        ];
    }
}
