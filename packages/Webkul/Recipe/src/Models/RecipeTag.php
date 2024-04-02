<?php

namespace Webkul\Recipe\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webkul\Tag\Models\Tag;
use Webkul\Recipe\Models\Recipe;

class RecipeTag extends Model
{
    use HasFactory;

    protected $table = 'recipe_tag';
    protected $guarded = [];

    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tag_id');
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }
}
