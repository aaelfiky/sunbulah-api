<?php

namespace Webkul\Recipe\Models;

use Webkul\Core\Eloquent\TranslatableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webkul\Recipe\Contracts\Recipe as RecipeContract;

class Recipe extends TranslatableModel implements RecipeContract
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['translations'];

    

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

    // Relation with Tags 

    // Relation with Ratings 

    // Relation with Products 
    public function products()
    {
        return $this->hasMany(RecipeProduct::class, 'recipe_id');
    }

    public function locales()
    {
        return $this->hasMany(RecipeTranslation::class, 'recipe_id');
    }

    // Relation with Topics
}
