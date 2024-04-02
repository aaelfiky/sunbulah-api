<?php

namespace Webkul\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webkul\Recipe\Models\Recipe;

class UserRecipe extends Model
{
    use HasFactory;

    protected $table = 'user_recipes';

    protected $guarded = [];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
