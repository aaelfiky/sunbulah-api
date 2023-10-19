<?php

Route::group([
        'prefix'     => 'support',
        'middleware' => ['web', 'theme', 'locale', 'currency']
    ], function () {

        Route::get('/', 'Webkul\Support\Http\Controllers\Shop\SupportController@index')->defaults('_config', [
            'view' => 'support::shop.index',
        ])->name('shop.support.index');

});