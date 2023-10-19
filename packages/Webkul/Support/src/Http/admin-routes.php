<?php

Route::group([
        'prefix'        => 'admin/support',
        'middleware'    => ['web', 'admin']
    ], function () {

        Route::get('', 'Webkul\Support\Http\Controllers\Admin\SupportController@index')->defaults('_config', [
            'view' => 'support::admin.index',
        ])->name('admin.support.index');

        Route::get('/{id}', 'Webkul\Support\Http\Controllers\Admin\SupportController@edit')->defaults('_config', [
            'view' => 'support::admin.edit',
        ])->name('admin.support.edit');

        Route::put('/{id}', 'Webkul\Support\Http\Controllers\Admin\SupportController@update')->defaults('_config', [
            'view' => 'support::admin.edit',
        ])->name('admin.support.update');

        Route::delete('/{id}', 'Webkul\Support\Http\Controllers\Admin\SupportController@delete')->name('admin.support.delete');

});