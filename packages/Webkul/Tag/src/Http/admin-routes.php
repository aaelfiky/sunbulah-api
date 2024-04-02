<?php

Route::group([
        'prefix'        => 'admin/tag',
        'middleware'    => ['web', 'admin']
    ], function () {

        Route::get('', 'Webkul\Tag\Http\Controllers\Admin\TagController@index')->defaults('_config', [
            'view' => 'tag::admin.index',
        ])->name('admin.tag.index');

});