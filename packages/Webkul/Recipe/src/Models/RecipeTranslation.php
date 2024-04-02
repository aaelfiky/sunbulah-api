<?php

namespace Webkul\Recipe\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductProxy;
use Webkul\Recipe\Models\RecipeProxy;
use Illuminate\Database\Eloquent\Model;

class RecipeTranslation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'seo' => 'json',
        'instructions' => 'array',
        'ingredients' => 'array',
        'tags' => 'array',
        'author' => 'json',
        'recipe_card' => 'json'
    ];

    public function recipe()
    {
        return $this->belongsTo(RecipeProxy::modelClass(), 'main_product_id');
    }
}
