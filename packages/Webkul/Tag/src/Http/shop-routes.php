<?php

Route::group([
        'prefix'     => 'tag',
        'middleware' => ['web', 'theme', 'locale', 'currency']
    ], function () {

        Route::get('/', 'Webkul\Tag\Http\Controllers\Shop\TagController@index')->defaults('_config', [
            'view' => 'tag::shop.index',
        ])->name('shop.tag.index');

});