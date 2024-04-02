<?php

namespace Webkul\Tag\Repositories;

use Illuminate\Container\Container as App;
use Illuminate\Support\Facades\DB;
use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\Tag\Models\TagTranslationProxy;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TagRepository extends Repository
{
    /**
     * Create a new repository instance.
     *
     * @param \Illuminate\Container\Container $app
     *
     * @return void
     */
    public function __construct(
        App $app
    )
    {
        parent::__construct($app);
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    function model()
    {
        return 'Webkul\Tag\Contracts\Tag';
    }


    public function getAll()
    {
        
        $query = $this->model->query();

        if (request()->has('name')) {
            $query = $query->whereHas('locales', function ($q) {
                $q->where('name', 'like', '%' . request()->input("name") . '%')->where('locale', core()->getRequestedLocaleCode());
            });
        }

        return $query->get();
    }
}
