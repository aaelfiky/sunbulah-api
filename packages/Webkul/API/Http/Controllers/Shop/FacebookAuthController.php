<?php
namespace Webkul\API\Http\Controllers\Shop;

use App\Helpers\AuthHelper;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JWTAuth;
use Laravel\Socialite\Facades\Socialite;
use Webkul\API\Http\Resources\Customer\Customer as CustomerResource;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\SocialLogin\Models\CustomerSocialAccount;
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
        
            $response = Http::get("https://graph.facebook.com/v18.0/oauth/access_token", [
                "client_id" => config('services.facebook.client_id'),
                "redirect_uri" => config('services.facebook.redirect'),
                "client_secret" => config('services.facebook.client_secret'),
                "code" => $request->get('code')
            ]);

            $accessToken = $response->json()["access_token"];

            $fields = 'id,name,email'; // Specify the fields you want to retrieve

            $user_response = Http::get("https://graph.facebook.com/v12.0/me", [
                "fields" => $fields,
                "access_token" => $accessToken
            ]);


            if ($user_response->status() == 200) {
                logger([
                    "info" => "Success User",
                    "user" => $user_response->json()
                ]);
            }

            $userData = $user_response->json();

            // Access the user data
            $userID = $userData['id'];
            $userName = $userData['name'];
            $userEmail = $userData['email'];

            // Use the user data as needed
            $customer = Customer::where('provider_name', '=', 'facebook')
                ->where('provider_id', '=', $userID)
                ->first();

            if (!$customer) {
                $customer = Customer::create([
                        'provider_id' => $userID,
                        'provider_name' => 'facebook',
                        'customer_group_id' => CustomerGroup::GENERAL,
                        'first_name' => $userName,
                        'email' => $userEmail
                    ]);
                
                $social_account = CustomerSocialAccount::create([
                        'customer_id'   => $customer->id,
                        'provider_id'   => $userID,
                        'provider_name' => "facebook"
                    ]);
            }

            auth()->guard('customer')->login($customer, true);
            $customer = auth('customer')->user();

            $jwtToken = JWTAuth::fromUser($customer);

            // return response()->json([
            //     'token'   => AuthHelper::getAuthTokenData($jwtToken),
            //     'message' => 'Logged in with facebook successfully.',
            //     'data'    => new CustomerResource($customer),
            // ], 201);

            $shop_url = "https://" . env('SHOP_BASE_URL');

            return redirect()->away("$shop_url?access_token=$jwtToken");
        
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