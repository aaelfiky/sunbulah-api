<?php

Route::group([
        'prefix'     => 'topic',
        'middleware' => ['web', 'theme', 'locale', 'currency']
    ], function () {

        Route::get('/', 'Webkul\Topic\Http\Controllers\Shop\TopicController@index')->defaults('_config', [
            'view' => 'topic::shop.index',
        ])->name('shop.topic.index');

});