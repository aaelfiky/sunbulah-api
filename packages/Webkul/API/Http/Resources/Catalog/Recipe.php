<?php

namespace Webkul\API\Http\Resources\Catalog;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class Recipe extends JsonResource
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
            'name'               => $this->name,
            'preparation_time'   => $this->preparation_time,
            'serves'             => $this->serves,
            'cooking_time'       => $this->cooking_time,
            'video'              => $this->video,
            'video_link'         => $this->video_link,
            'image_desktop'      => $this->image_desktop,
            'image_mobile'       => $this->image_mobile,
            'instructions'       => $this->instructions,
            'ingredients'        => $this->ingredients,
            'notes'              => $this->notes,
            'RecipeCard'         => $this->recipe_card,
            'MainProduct'        => $this->product,
            'seo'                => $this->seo,
            'author'             => $this->author,
            'tags'               => $this->tags,
            'products'           => $this->products
        ];
    }
}
