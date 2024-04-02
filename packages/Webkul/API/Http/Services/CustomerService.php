<?php

namespace Webkul\API\Http\Services;

use App\Http\Connectors\StrapiConnector;
use Illuminate\Http\Request;
use Webkul\Customer\Models\UserProduct;
use Webkul\Customer\Models\UserRecipe;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerRepository;

class CustomerService
{
    /**
     * Contains current guard
     *
     * @var array
     */
    protected $guard;

    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * Repository object
     *
     * @var \Webkul\Customer\Repositories\CustomerRepository
     */
    protected $customerRepository;

    /**
     * Repository object
     *
     * @var \Webkul\Customer\Repositories\CustomerGroupRepository
     */
    protected $customerGroupRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Customer\Repositories\CustomerRepository  $customerRepository
     * @param  \Webkul\Customer\Repositories\CustomerGroupRepository  $customerGroupRepository
     * @return void
     */
    public function __construct(
        CustomerRepository $customerRepository,
        CustomerGroupRepository $customerGroupRepository
    )   {
        $this->guard = request()->has('token') ? 'api' : 'customer';

        $this->_config = request('_config');

        if (isset($this->_config['authorization_required']) && $this->_config['authorization_required']) {

            auth()->setDefaultDriver($this->guard);

            $this->middleware('auth:' . $this->guard);
        }

        $this->customerRepository = $customerRepository;

        $this->customerGroupRepository = $customerGroupRepository;
    }

    public function getFavoriteRecipes(Request $request = null)
    {
        if (is_null(auth()->guard($this->guard)->user())) {
            return [];
        }
        
        $recipes = UserRecipe::with("recipe")->where("customer_id", auth()->guard($this->guard)->user()->id)->get();
        $results = [];

        foreach ($recipes as $recipe) {
            array_push($results, $recipe->recipe);
        }
        
        return $results;
    }

    public function getFavoriteProducts(Request $request = null)
    {
        $products = UserProduct::with("product")->where("customer_id", auth()->guard($this->guard)->user()->id)->get();
        $results = [];

        foreach ($products as $product) {
            array_push($results, $product->product);
        }
        
        return $results;
    }
}