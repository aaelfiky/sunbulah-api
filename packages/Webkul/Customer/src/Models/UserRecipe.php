<?php

namespace Webkul\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRecipe extends Model
{
    use HasFactory;

    protected $table = 'user_recipes';

    protected $guarded = [];
}
