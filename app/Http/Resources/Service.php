<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Http\Resources\EquipmentCollection;
use App\ServiceFrequency;

class Service extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $all_services_frequencies = ServiceFrequency::get();
        if ($all_services_frequencies->count() > 0)
            $all_services_frequencies->map(function ($i) {
                $i->service_id = $this->id;
            });

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image ? url('assets/services') . '/' . $this->image : '',
            'status' => intval($this->status),
            'service_question' => $this->service_question ?? "",
            'question' => $this->service_additional_questions->count() > 0 ? QuestionResource::collection($this->service_additional_questions) : [],
            'service_frequency' => ($all_services_frequencies->count() > 0) ? ServiceFrequenciesResource::collection($all_services_frequencies) : []
        ];
    }
}
