<?php

namespace Webkul\API\Http\Controllers\Shop;

use Webkul\Tag\Repositories\TagRepository;
use Webkul\Tag\Models\Tag;
use Webkul\API\Http\Resources\Catalog\Tag as TagResource;

class TagController extends Controller
{
    /**
     * TagRepository object
     *
     * @var \Webkul\Tag\Repositories\TagRepository
     */
    protected $tagRepository;
    
    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return TagResource::collection($this->tagRepository->getAll());
    }

}