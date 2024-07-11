<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class Notification extends Resource
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
            'id' => $this->id ? $this->id : '',
            'userId' => $this->user_id ? $this->user_id : '',
            'typeId' => $this->type_id ? $this->type_id : '',
            'type' => $this->type ? $this->type : '',
            'title' => $this->title ? $this->title : '',
            'message' => $this->message ? $this->message : '',
            'isRead' => $this->is_read ? $this->is_read : '',
            'createdAt' => $this->created_at ? $this->created_at->format('Y-m-d') : ''
        ];
    }
}
