<?php

Route::group([
        'prefix'     => 'recipe',
        'middleware' => ['web', 'theme', 'locale', 'currency']
    ], function () {

        Route::get('/', 'Webkul\Recipe\Http\Controllers\Shop\RecipeController@index')->defaults('_config', [
            'view' => 'recipe::shop.index',
        ])->name('shop.recipe.index');

});