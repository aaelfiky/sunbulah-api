<?php

namespace Webkul\API\Http\Controllers\Shop;

use Webkul\Topic\Repositories\TopicRepository;
use Webkul\Topic\Models\Topic;
use Webkul\API\Http\Resources\Catalog\Topic as TopicResource;

class TopicController extends Controller
{
    /**
     * TopicRepository object
     *
     * @var \Webkul\Topic\Repositories\TopicRepository
     */
    protected $topicRepository;
    
    public function __construct(TopicRepository $topicRepository)
    {
        $this->topicRepository = $topicRepository;
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return TopicResource::collection($this->topicRepository->getAll());
    }

}