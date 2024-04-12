<?php

Route::group([
        'prefix'        => 'admin/topic',
        'middleware'    => ['web', 'admin']
    ], function () {

        Route::get('', 'Webkul\Topic\Http\Controllers\Admin\TopicController@index')->defaults('_config', [
            'view' => 'topic::admin.index',
        ])->name('admin.topic.index');

});