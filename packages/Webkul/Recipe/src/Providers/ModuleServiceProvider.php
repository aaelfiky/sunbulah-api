<?php

namespace Webkul\Recipe\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\Recipe\Models\Recipe::class,
    ];
}