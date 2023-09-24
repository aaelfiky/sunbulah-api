<?php
namespace Webkul\API\Http\Controllers\Shop;

use GuzzleHttp\Exception\ClientException;
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
use GuzzleHttp\Exception\RequestException;
/**
 * Facebook Controller
 *
 */
class FacebookAuthController extends Controller
{
    public function redirectFacebook()
    {
        return response()->json([
            'url'   => Socialite::driver('facebook')->redirect()->getTargetUrl(),
            'message' => 'Facebook URL Generated Successfully'
        ], 200);
    }

    public function facebookCallback(Request $request)
    {
        try {
        
            logger([
                "info" => "here"
            ]);
            $facebook_user = Socialite::driver('facebook')->user();
         
            logger([
                "facebook" => $facebook_user
            ]);

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
        
        } catch (ClientException $e) {
            // Caught a Guzzle ClientException
            Log::error($e->getMessage());
            return response()->json([
                'message' => $e->getMessage(),
                'info' => "Client Exception",
                'data'    => null,
            ], 400);
        } catch (RequestException $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => $e->getMessage(),
                'info' => "Request Exception",
                'data'    => null,
            ], 400);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => $e,
                'info' => "Generic Exception",
                'data'    => null,
            ], 400);
        }
    }
}