<?php

namespace Webkul\Topic\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\Topic\Models\Topic::class,
    ];
}