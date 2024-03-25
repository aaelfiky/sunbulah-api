<?php

namespace Webkul\Recipe\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webkul\Product\Models\Product;
use Webkul\Recipe\Models\Recipe;

class RecipeProduct extends Model
{
    use HasFactory;

    protected $table = 'recipe_product';
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }
}
