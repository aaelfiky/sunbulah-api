<?php

namespace Webkul\Tag\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Webkul\Tag\Repositories\TagRepository;

class TagController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

     /**
     * Tag repository instance.
     *
     * @var \Webkul\Tag\Repositories\TagRepository
     */
    protected $tagRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        TagRepository $tagRepository
    )
    {
        $this->middleware('admin');

        $this->_config = request('_config');

        $this->tagRepository = $tagRepository;
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
            'slug'        => ['required', 'unique:tag_translations,slug'],
            'name'        => 'required'
        ]);

        $tag = $this->tagRepository->create(request()->all());


        session()->flash('success', trans('admin::app.response.create-success', ['name' => 'Tag']));

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
        $tag = $this->tagRepository->findOrFail($id);

        return view($this->_config['view'], compact("tag"));
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

        $this->tagRepository->update(request()->all(), $id);

        session()->flash('success', trans('admin::app.response.update-success', ['name' => 'Tag']));

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
        $tag = $this->tagRepository->findOrFail($id);

        if (!$this->isTagDeletable($tag)) {
            session()->flash('warning', trans('admin::app.response.delete-tag-root', ['name' => 'Tag']));
        } else {
            try {
                Event::dispatch('catalog.tag.delete.before', $tag);
                
                $tag->products()->delete();

                $tag->locales()->delete();

                $tag->delete();
                
                Event::dispatch('catalog.tag.delete.after', $tag);

                session()->flash('success', trans('admin::app.response.delete-success', ['name' => 'Tag']));

                return response()->json(['message' => true], 200);
            } catch (\Exception $e) {
                session()->flash('error', trans('admin::app.response.delete-failed', ['name' => 'Tag']));
            }
        }

        return response()->json(['message' => false], 400);
    }


    /**
     * Check whether the current tag is deletable or not.
     *
     * This method will fetch all root tag ids from the channel. If `id` is present,
     * then it is not deletable.
     *
     * @param  \Webkul\Tag\Models\Tag $tag
     * @return bool
     */
    private function isTagDeletable($tag)
    {
        return true;
    }
}
