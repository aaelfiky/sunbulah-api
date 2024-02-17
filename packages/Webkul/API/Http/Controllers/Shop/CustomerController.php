<?php

namespace Webkul\API\Http\Controllers\Shop;

use App\Http\Connectors\StrapiConnector;
use App\Http\Connectors\XMLConnector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Support\Str;
use Webkul\Customer\Http\Requests\CustomerRegistrationRequest;
use Webkul\Customer\Mail\VerificationEmail;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\UserProduct;
use Webkul\Customer\Models\Wishlist;
use Webkul\Customer\Models\UserRecipe;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\API\Http\Services\CustomerService;

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
     * Service object
     *
     * @var CustomerService
     */
    protected $customerService;

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
        CustomerGroupRepository $customerGroupRepository,
        CustomerService $customerService
    ) {
        $this->guard = request()->has('token') ? 'api' : 'customer';

        $this->_config = request('_config');

        if (isset($this->_config['authorization_required']) && $this->_config['authorization_required']) {

            auth()->setDefaultDriver($this->guard);

            $this->middleware('auth:' . $this->guard);
        }

        $this->customerRepository = $customerRepository;

        $this->customerGroupRepository = $customerGroupRepository;

        $this->customerService = $customerService;
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
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'email' => $email,
            'phone' => $request->get('phone'),
            'password' => $request->get('password'),
            'password' => bcrypt($request->get('password')),
            'token' => $verification_token,
            'channel_id' => core()->getCurrentChannel()->id,
            'is_verified' => core()->getConfigData('customer.settings.email.verification') ? 0 : 1,
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

    public function generateQRCode(Request $request)
    {
        // Initialise the 2FA class
        $google2fa = new Google2FA();
        // Save the registration data in an array
        $registration_data = $request->all();

        $user = auth($this->guard)->user();

        $registration_data["email"] = $user->email;

        // Add the secret key to the registration data
        $registration_data["google2fa_secret"] = is_null($user->google2fa_secret) ? $google2fa->generateSecretKey() : $user->google2fa_secret;

        // Save the registration data to the user session for just the next request
        $request->session()->flash('registration_data', $registration_data);

        // Generate the QR image. This is the image the user will scan with their app
        // to set up two factor authentication
        $QR_Image = $this->generateQRCodeUrl(
            $registration_data["email"],
            $registration_data["google2fa_secret"]
        );

        if (is_null($user->google2fa_secret)) {
            $user->google2fa_secret = $registration_data["google2fa_secret"];
            $user->save();
        }

        // Pass the QR barcode image to our view
        return response()->json([
            'data' => [
                'QR_Image' => $QR_Image,
                'secret' => $registration_data['google2fa_secret']
            ],
        ]);
    }


    public function verifyQRCode(Request $request)
    {
        $google2fa = new Google2FA();
        $user = auth($this->guard)->user();
        $user_2fa_token = $user->google2fa_secret;


        $valid = $google2fa->verifyKey($user_2fa_token, $request->get('code'));

        if ($valid) {
            if (!$user->two_factor_verified) {
                $user->two_factor_verified = true;
                $user->save();
            }
            return response()->json([
                'message' => "Successful two factor authentication",
            ]);
        }

        return response()->json([
            'message' => "invalid token",
        ], 400);
    }


    private function generateQRCodeUrl(string $email, string $secret)
    {
        $base_url = env('SHOP_BASE_URL');
        return "https://api.qrserver.com/v1/create-qr-code/?size=193x193&data=otpauth://totp/$base_url:$email?secret=$secret&issuer=$base_url&digits=6";
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
            $favoriteProducts = $this->customerService->getFavoriteProducts();
            $favoriteRecipes = $this->customerService->getFavoriteRecipes();
            $customer = $this->customerRepository->findOrFail($id);
            $customer->favorite_products = $favoriteProducts;
            $customer->favorite_recipes = $favoriteRecipes;

            return new $this->_config['resource'](
                $customer
            );
        }

        return response()->json([
            'message' => 'Invalid Request.',
        ], 403);
    }
    public function favoriteProduct($id)
    {
        $message = "";
        $product = UserProduct::firstWhere([
            "product_id" => $id,
            "customer_id" => auth()->guard($this->guard)->user()->id
        ]);
        if ($product) {
            $product->delete();
            $message = "Product removed from favorites successfully";
        } else {
            UserProduct::create([
                "product_id" => $id,
                "customer_id" => auth()->guard($this->guard)->user()->id
            ]);
            $message = "Product added to favorites successfully";
        }

        return response()->json([
            "message" => $message
        ]);
    }

    public function favoriteRecipe($id)
    {
        $message = "";
        $product = UserRecipe::firstWhere([
            "recipe_id" => $id,
            "customer_id" => auth()->guard($this->guard)->user()->id
        ]);
        if ($product) {
            $product->delete();
            $message = "Recipe removed from favorites successfully";
        } else {
            UserRecipe::create([
                "recipe_id" => $id,
                "customer_id" => auth()->guard($this->guard)->user()->id
            ]);
            $message = "Recipe added to favorites successfully";
        }

        return response()->json([
            "message" => $message
        ]);
    }

    public function getFavoriteRecipes(Request $request)
    {
        $favorites = $this->customerService->getFavoriteRecipes($request);

        return response()->json([
            "message" => "Favorite Recipes fetched successfully",
            "data" => $favorites
        ]);
    }

    public function getFavoriteProducts(Request $request)
    {
        $favorites = $this->customerService->getFavoriteProducts($request);

        return response()->json([
            "message" => "Favorite products fetched successfully",
            "data" => $favorites
        ]);
    }

    public function getProducts(Request $request) {
        XMLConnector::syncProducts();
        return response()->json([
            "message" => "Products Imported Successfully",
            "data" => true
        ]);
    }

    public function delete(Request $request)
    {
        try {
            $customer = Customer::findOrFail(auth()->guard($this->guard)->user()->id);
            $customer->email = $customer->email . "+d_" . now();
            $customer->save();
            $customer->delete();

            // TODO: Delete all user related entries

            return response()->json([
                "message" => "Customer deleted successfully",
                "data" => true
            ]);
        } catch (\Exception $exception) {
            logger(
                "failed to delete customer",
                [
                    "error" => $exception->getMessage()
                ]
            );
            return response()->json([
                "message" => $exception->getMessage(),
                "data" => false
            ]);
        }
    }
}