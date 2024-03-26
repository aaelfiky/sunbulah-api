<?php

namespace Webkul\Recipe\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeTranslation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'seo' => 'json',
        'instructions' => 'array',
        'ingredients' => 'array',
        'author' => 'json',
        'recipe_card' => 'json'
    ];
}
