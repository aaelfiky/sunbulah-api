<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Support\Facades\Event;
use Webkul\API\Http\Resources\Customer\Customer as CustomerResource;
use Illuminate\Support\Facades\Mail;
use Webkul\API\Http\Services\CustomerService;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Http\Requests\CustomerLoginRequest;
use Webkul\Customer\Mail\VerificationEmail;
use Webkul\Customer\Repositories\CustomerRepository;

class SessionController extends Controller
{
    /**
     * Contains current guard
     *
     * @var string
     */
    protected $guard;

    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

    protected $customerService;

    /**
     * Controller instance
     *
     * @param  \Webkul\Customer\Repositories\CustomerRepository  $customerRepository
     */
    public function __construct(CustomerRepository $customerRepository, CustomerService $customerService)
    {
        $this->guard = request()->has('token') ? 'api' : 'customer';

        auth()->setDefaultDriver($this->guard);

        $this->middleware('auth:' . $this->guard, ['only' => ['get', 'update', 'destroy']]);

        $this->_config = request('_config');

        $this->customerRepository = $customerRepository;

        $this->customerService = $customerService;
    }

    /**
     * Method to store user's sign up form data to DB.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CustomerLoginRequest $request)
    {
        $request->validated();

        $jwtToken = null;

        if (!$jwtToken = auth()->guard($this->guard)->attempt($request->only(['email', 'password']))) {
            return response()->json([
                'error' => 'Invalid Email or Password',
            ], 401);
        }

        Event::dispatch('customer.after.login', $request->get('email'));

        $customer = auth($this->guard)->user();

        if (!$customer->is_verified) {
            if (core()->getConfigData('customer.settings.email.verification')) {
                $token = md5(uniqid(rand(), true));
                $customer->update(["token" => $token]);
                try {
                    Mail::to($request->get('email'))->send(new VerificationEmail(['email' => $request->get('email'), 'token' => $token]));
                } catch (\Throwable $th) {
                    logger([
                        "message" => "failed to send email"
                    ]);
                }
                return response()->json([
                    'message' => 'Your email is not verified yet.',
                    'is_verified' => false,
                    'data' => new CustomerResource($customer)
                ]);
            }
        }

        return response()->json([
            'token' => $jwtToken,
            'message' => 'Logged in successfully.',
            'is_verified' => true,
            'data' => new CustomerResource($customer),
            'two_factor_enabled' => core()->getConfigData('customer.settings.two_factor_authentication.verification') ? true : false
        ]);
    }

    /**
     * Get details for current logged in customer
     *
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        $customer = auth($this->guard)->user();

        return response()->json([
            'data' => new CustomerResource($customer),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        $customer = auth($this->guard)->user();

        $this->validate(request(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'date_of_birth' => 'nullable|date|before:today',
            'email' => 'email|unique:customers,email,' . $customer->id,
            'password' => 'confirmed|min:6',
        ]);

        $data = request()->only('first_name', 'last_name', 'gender', 'date_of_birth', 'email', 'password');

        if (!isset($data['password']) || !$data['password']) {
            unset($data['password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }

        $updatedCustomer = $this->customerRepository->update($data, $customer->id);

        return response()->json([
            'message' => 'Your account has been updated successfully.',
            'data' => new CustomerResource($updatedCustomer),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        auth()->guard($this->guard)->logout();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
