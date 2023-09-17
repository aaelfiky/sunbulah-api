<?php
namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JWTAuth;
use JWTFactory;
use Webkul\API\Http\Resources\Customer\Customer as CustomerResource;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\SocialLogin\Models\CustomerSocialAccount;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
/**
 * Facebook Controller
 *
 */
class FacebookAuthController extends Controller
{
    public function redirectFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function facebookCallback()
    {
        try {
        
            $facebook_user = Socialite::driver('facebook')->user();
         

            $customer = Customer::where('provider_name', '=', 'facebook')
                ->where('provider_id', '=', $facebook_user->id)
                ->first();

            /**
             */
            if (!$customer) {
                $customer = Customer::create([
                        'provider_id' => $facebook_user->id,
                        'provider_name' => 'facebook',
                        'customer_group_id' => CustomerGroup::GENERAL,
                        'first_name' => $facebook_user->name,
                        'email' => $facebook_user->email
                        //'avatar' => $providerUser->picture, // in case you have an avatar and want to use google's
                    ]);
                
                $social_account = CustomerSocialAccount::create([
                        'customer_id'   => $customer->id,
                        'provider_id'   => $facebook_user->id,
                        'provider_name' => "google"
                    ]);
            }

            auth()->guard('customer')->login($customer, true);
            $customer = auth('customer')->user();

            $jwtToken = JWTAuth::fromUser($customer);

            return response()->json([
                'token'   => $this->getAuthTokenData($jwtToken),
                'message' => 'Logged in with facebook successfully.',
                'data'    => new CustomerResource($customer),
            ], 201);
        
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => $e->getMessage(),
                'data'    => null,
            ], 400);
        }
    }
}