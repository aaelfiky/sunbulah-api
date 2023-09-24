<?php
namespace Webkul\API\Http\Controllers\Shop;

use App\Helpers\AuthHelper;
use Google_Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JWTAuth;
use Webkul\API\Http\Resources\Customer\Customer as CustomerResource;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\SocialLogin\Models\CustomerSocialAccount;

/**
 * Google Controller
 *
 */
class GoogleAuthController extends Controller
{
    /**
     * Return the url of the google auth.
     * FE should call this and then direct to this url.
     *
     * @return JsonResponse
     */
    public function getAuthUrl(Request $request):JsonResponse
    {
        /**
         * Create google client
         */
        $client = $this->getClient();

        /**
         * Generate the url at google we redirect to
         */
        $authUrl = $client->createAuthUrl();

        /**
         * HTTP 200
         */
        return response()->json([
            "data" => $authUrl
        ], 200);
    } // getAuthUrl


    /**
     * Login and register
     * Gets registration data by calling google Oauth2 service
     *
     * @return JsonResponse
     */
    public function postLogin(Request $request):JsonResponse
    {

        /**
         * Get authcode from the query string
         */
        $authCode = urldecode($request->input('auth_code'));

        /**
         * Google client
         */
        $client = $this->getClient();

        /**
         * Exchange auth code for access token
         * Note: if we set 'access type' to 'force' and our access is 'offline', we get a refresh token. we want that.
         */
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        /**
         * Set the access token with google. nb json
         */

        $googleAccessTokenObject = (object) $accessToken;
        if (!isset($googleAccessTokenObject->access_token)) {
            return response()->json(["message" => "failed to perform google social login", "error" => $googleAccessTokenObject], 400);
        }
        $client->setAccessToken($googleAccessTokenObject->access_token);

        /**
         * Get user's data from google
         */
        $service = new \Google\Service\Oauth2($client);
        $userFromGoogle = $service->userinfo->get();

        /**
         * Select user if already exists
         */
        $customer = Customer::where('provider_name', '=', 'google')
            ->where('provider_id', '=', $userFromGoogle->id)
            ->first();

        /**
         */
        if (!$customer) {
            $customer = Customer::create([
                    'provider_id' => $userFromGoogle->id,
                    'provider_name' => 'google',
                    'customer_group_id' => CustomerGroup::GENERAL,
                    'google_access_token' => json_encode($accessToken),
                    'first_name' => $userFromGoogle->name,
                    'email' => $userFromGoogle->email
                    //'avatar' => $providerUser->picture, // in case you have an avatar and want to use google's
                ]);
            
            $social_account = CustomerSocialAccount::create([
                    'customer_id'   => $customer->id,
                    'provider_id'   => $userFromGoogle->id,
                    'provider_name' => "google"
                ]);
        }
        /**
         * Save new access token for existing user
         */
        else {
            $customer->google_access_token = json_encode($accessToken);
            $customer->save();
        }

        /**
         * Log in and return token
         * HTTP 201
         */

        auth()->guard('customer')->login($customer, true);
        $customer = auth('customer')->user();


        $jwtToken = JWTAuth::fromUser($customer);

        return response()->json([
            'token'   => AuthHelper::getAuthTokenData($jwtToken),
            'message' => 'Logged in successfully.',
            'data'    => new CustomerResource($customer),
        ], 201);
    } // postLogin



    /**
     * Get meta data on a page of files in user's google drive
     *
     * @return JsonResponse
     */
    public function getDrive(Request $request):JsonResponse
    {
        /**
         * Get google api client for session user
         */
        $client = $this->getUserClient();

        /**
         * Create a service using the client
         * @see vendor/google/apiclient-services/src/
         */
        $service = new \Google\Service\Drive($client);

        /**
         * The arguments that we pass to the google api call
         */
        $parameters = [
            'pageSize' => 10,
        ];

        /**
         * Call google api to get a list of files in the drive
         */
        $results = $service->files->listFiles($parameters);

        /**
         * HTTP 200
         */
        return response()->json($results, 200);
    }


    /**
     * Gets a google client
     *
     * @return \Google_Client
     */
    private function getClient():\Google_Client
    {
        // load our config.json that contains our credentials for accessing google's api as a json string
        $configJson = base_path().'/google_config.json';

        // define an application name
        $applicationName = 'sunbulah-app';

        // create the client
        $client = new \Google_Client();
        $client->setApplicationName($applicationName);
        $client->setAuthConfig($configJson);
        $client->setAccessType('offline'); // necessary for getting the refresh token
        $client->setApprovalPrompt('force'); // necessary for getting the refresh token
        // scopes determine what google endpoints we can access. keep it simple for now.
        $client->setScopes(
            [
                \Google\Service\Oauth2::USERINFO_PROFILE,
                \Google\Service\Oauth2::USERINFO_EMAIL,
                \Google\Service\Oauth2::OPENID,
                \Google\Service\Drive::DRIVE_METADATA_READONLY
            ]
        );
        $client->setIncludeGrantedScopes(true);
        return $client;
    } // getClient


    /**
     * Returns a google client that is logged into the current user
     *
     * @return \Google_Client
     */
    private function getUserClient():\Google_Client
    {
        /**
         * Get Logged in user
         */
        $user = Customer::where('id', '=', auth()->guard('api')->user()->id)->first();

        /**
         * Strip slashes from the access token json
         * if you don't strip mysql's escaping, everything will seem to work
         * but you will not get a new access token from your refresh token
         */
        $accessTokenJson = stripslashes($user->google_access_token);

        /**
         * Get client and set access token
         */
        $client = $this->getClient();
        $client->setAccessToken($accessTokenJson);

        /**
         * Handle refresh
         */
        if ($client->isAccessTokenExpired()) {
            // fetch new access token
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $client->setAccessToken($client->getAccessToken());

            // save new access token
            $user->google_access_token = json_encode($client->getAccessToken());
            $user->save();
        }

        return $client;
    } // getUserClient
}