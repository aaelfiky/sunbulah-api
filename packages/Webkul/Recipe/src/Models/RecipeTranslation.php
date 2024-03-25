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
        'instructions' => 'json'
    ];
}
