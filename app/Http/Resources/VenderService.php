<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Http\Resources\ServiceCollection;
use App\Http\Resources\Service;

class VenderService extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //$venderServiceCollection = new ServiceCollection($this->service);
        return [
            'id' => $this->id,
            'venderId' => $this->vender_id,
            'serviceId' => $this->service_id,
            'serviceName' => $this->service->title,
            'image' => $this->service->image ? url('/assets/services') . '/' . $this->service->image : '',

            'serviceData' =>  new Service($this->service)
        ];
    }
}
