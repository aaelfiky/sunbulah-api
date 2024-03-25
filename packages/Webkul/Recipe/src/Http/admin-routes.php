<?php

Route::group([
        'prefix'        => 'admin/recipe',
        'middleware'    => ['web', 'admin']
    ], function () {

        Route::get('', 'Webkul\Recipe\Http\Controllers\Admin\RecipeController@index')->defaults('_config', [
            'view' => 'recipe::admin.index',
        ])->name('admin.recipe.index');

});