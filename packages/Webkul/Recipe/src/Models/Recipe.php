<?php

namespace Webkul\Recipe\Models;

use Webkul\Core\Eloquent\TranslatableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webkul\Product\Models\Product;
use Webkul\Tag\Models\Tag;
use Webkul\Product\Models\ProductProxy;
use Webkul\Topic\Models\TopicProxy;
use Webkul\Recipe\Contracts\Recipe as RecipeContract;

class Recipe extends TranslatableModel implements RecipeContract
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['product'];

    public $translatedAttributes = [
        'name',
        'slug',
        'preparation_time',
        'serves',
        'cooking_time',
        'video',
        'video_link',
        'image_desktop',
        'image_mobile',
        'instructions',
        'ingredients',
        'notes',
        'recipe_card',
        'main_product_id',
        'recipe_id',
        'seo'
    ];

    protected $appends = ["similar_recipes"];

    // Relation with Tags 

    // Relation with Ratings 

    // Relation with Products 
    public function products()
    {
        return $this->belongsToMany(Product::class, 'recipe_product', 'recipe_id', 'product_id');
    }

    public function locales()
    {
        return $this->hasMany(RecipeTranslation::class, 'recipe_id');
    }

    // Relation with Tags 
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'recipe_tag', 'recipe_id', 'tag_id');
    }

       /**
     * Get the product that owns the image.
     */
    public function product()
    {
        return $this->belongsTo(ProductProxy::modelClass(), 'main_product_id');
    }

    /**
     * Get the product that owns the image.
     */
    public function topic()
    {
        return $this->belongsTo(TopicProxy::modelClass(), 'topic_id');
    }


    public function getSimilarRecipesAttribute()
    {
        return RecipeTranslation::where([
            "locale" => core()->getRequestedLocaleCode(),
            'main_product_id' => $this->product->id
        ])->get();
    }

    // Relation with Topics
}
