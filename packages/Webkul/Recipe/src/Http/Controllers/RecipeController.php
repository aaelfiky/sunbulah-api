<?php

namespace Webkul\Recipe\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Webkul\Recipe\Repositories\RecipeRepository;

class RecipeController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

     /**
     * Recipe repository instance.
     *
     * @var \Webkul\Recipe\Repositories\RecipeRepository
     */
    protected $recipeRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        RecipeRepository $recipeRepository
    )
    {
        $this->middleware('admin');

        $this->_config = request('_config');

        $this->recipeRepository = $recipeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view($this->_config['view']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view($this->_config['view']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {

        $this->validate(request(), [
            'slug'        => ['required', 'unique:recipe_translations,slug'],
            'name'        => 'required'
        ]);

        $recipe = $this->recipeRepository->create(request()->all());


        session()->flash('success', trans('admin::app.response.create-success', ['name' => 'Recipe']));

        return redirect()->route($this->_config['redirect']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $recipe = $this->recipeRepository->findOrFail($id);

        return view($this->_config['view'], compact("recipe"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $locale = core()->getRequestedLocaleCode();

        $this->recipeRepository->update(request()->all(), $id);

        session()->flash('success', trans('admin::app.response.update-success', ['name' => 'Recipe']));

        return redirect()->route($this->_config['redirect']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function remove($id)
    {
        $recipe = $this->recipeRepository->findOrFail($id);

        if (!$this->isRecipeDeletable($recipe)) {
            session()->flash('warning', trans('admin::app.response.delete-recipe-root', ['name' => 'Recipe']));
        } else {
            try {
                Event::dispatch('catalog.recipe.delete.before', $recipe);
                
                $recipe->products()->delete();

                $recipe->locales()->delete();

                $recipe->delete();
                
                Event::dispatch('catalog.recipe.delete.after', $recipe);

                session()->flash('success', trans('admin::app.response.delete-success', ['name' => 'Recipe']));

                return response()->json(['message' => true], 200);
            } catch (\Exception $e) {
                session()->flash('error', trans('admin::app.response.delete-failed', ['name' => 'Recipe']));
            }
        }

        return response()->json(['message' => false], 400);
    }


    /**
     * Check whether the current recipe is deletable or not.
     *
     * This method will fetch all root recipe ids from the channel. If `id` is present,
     * then it is not deletable.
     *
     * @param  \Webkul\Recipe\Models\Recipe $recipe
     * @return bool
     */
    private function isRecipeDeletable($recipe)
    {
        return true;
    }
}
