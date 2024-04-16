<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Jobs\SyncProducts;
use Webkul\Recipe\Repositories\RecipeRepository;
use Webkul\API\Http\Resources\Catalog\Recipe as RecipeResource;

class RecipeController extends Controller
{
    /**
     * RecipeRepository object
     *
     * @var \Webkul\Recipe\Repositories\RecipeRepository
     */
    protected $recipeRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Recipe\Repositories\RecipeRepository $recipeRepository
     * @return void
     */
    public function __construct(RecipeRepository $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return RecipeResource::collection($this->recipeRepository->getAll(request()->get('limit') ?? 10, request()->get('page') ?? 1));
    }

    /**
     * Returns a individual resource.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function getBySlug($slug)
    {
        return new RecipeResource(
            $this->recipeRepository->findBySlugOrFail($slug)
        );
    }

}
