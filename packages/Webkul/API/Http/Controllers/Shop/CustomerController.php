<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Webkul\Customer\Http\Requests\CustomerRegistrationRequest;
use Webkul\Customer\Mail\VerificationEmail;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerRepository;

class CustomerController extends Controller
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

    /**
     * Method to store user's sign up form data to DB.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CustomerRegistrationRequest $request)
    {
        $request->validated();

        $email = $request->get('email');

        $verification_token = md5(uniqid(rand(), true));

        $data = [
            'first_name'  => $request->get('first_name'),
            'last_name'   => $request->get('last_name'),
            'email'       => $email,
            'password'    => $request->get('password'),
            'password'    => bcrypt($request->get('password')),
            'token'       => $verification_token,
            'channel_id'  => core()->getCurrentChannel()->id,
            'is_verified' => core()->getConfigData('customer.settings.email.verification') ? 0 : 1,,
            'customer_group_id' => $this->customerGroupRepository->findOneWhere(['code' => 'general'])->id
        ];

        Event::dispatch('customer.registration.before');

        $customer = $this->customerRepository->create($data);

        if (Str::endsWith($email, '@sunbulah.com')) {
            $customer->update(['customer_group_id' => CustomerGroup::SUNBULAH_GROUP]);
        }

        Event::dispatch('customer.registration.after', $customer);

        if (core()->getConfigData('customer.settings.email.verification')) {
            Mail::queue(new VerificationEmail(['email' => $email, 'token' => $verification_token]));
                return response()->json([
                    'message' => 'Your email is created successfully but is not verified yet.'
                ]); 
        }

        return response()->json([
            'message' => 'Your account has been created successfully.',
        ]);
    }

    /**
     * Returns a current user data.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        if (Auth::user($this->guard)->id === (int) $id) {
            return new $this->_config['resource'](
                $this->customerRepository->findOrFail($id)
            );
        }

        return response()->json([
            'message' => 'Invalid Request.',
        ], 403);
    }
}