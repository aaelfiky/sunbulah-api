<?php

namespace Webkul\API\Http\Resources\Catalog;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class Topic extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                 => $this->id,
            'slug'               => $this->slug,
            'name'               => $this->name
        ];
    }
}
