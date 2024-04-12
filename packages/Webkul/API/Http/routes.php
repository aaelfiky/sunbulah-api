<?php
use Illuminate\Support\Facades\Mail;
use Webkul\Admin\Mail\OrderReceipt;
use Webkul\Sales\Models\Order;

Route::group(['prefix' => 'api'], function ($router) {

    // SOCIAL AUTH
    Route::group(['namespace' => 'Webkul\API\Http\Controllers\Shop'], function ($router) {
        Route::get('google/auth', 'GoogleAuthController@redirectToAuth');
        Route::get('google/auth/callback', 'GoogleAuthController@handleAuthCallback');

        Route::get('facebook/redirect', 'FacebookAuthController@redirectFacebook');
        Route::get('facebook/callback', 'FacebookAuthController@facebookCallback');
    });

    Route::group(['namespace' => 'Webkul\Support\Http\Controllers\API'], function ($router) {
        Route::get('orders-tickets/{id}', 'SupportController@getByOrderId');
        Route::post('orders-tickets/{id}', 'SupportController@updateByOrderId');
    });
    // END SOCIAL AUTH

    Route::group(['namespace' => 'Webkul\API\Http\Controllers\Shop', 'middleware' => ['locale', 'theme', 'currency']], function ($router) {
        //Currency and Locale switcher
        Route::get('switch-currency', 'CoreController@switchCurrency');

        Route::get('switch-locale', 'CoreController@switchLocale');


        //Category routes
        Route::get('categories', 'ResourceController@index')->defaults('_config', [
            'repository' => 'Webkul\Category\Repositories\CategoryRepository',
            'resource' => 'Webkul\API\Http\Resources\Catalog\Category'
        ]);

        Route::get('descendant-categories', 'CategoryController@index');

        Route::get('categories/{id}', 'ResourceController@get')->defaults('_config', [
            'repository' => 'Webkul\Category\Repositories\CategoryRepository',
            'resource' => 'Webkul\API\Http\Resources\Catalog\Category'
        ]);


        //Attribute routes
        Route::get('attributes', 'ResourceController@index')->defaults('_config', [
            'repository' => 'Webkul\Attribute\Repositories\AttributeRepository',
            'resource' => 'Webkul\API\Http\Resources\Catalog\Attribute'
        ]);

        Route::get('attributes/{id}', 'ResourceController@get')->defaults('_config', [
            'repository' => 'Webkul\Attribute\Repositories\AttributeRepository',
            'resource' => 'Webkul\API\Http\Resources\Catalog\Attribute'
        ]);


        //AttributeFamily routes
        Route::get('families', 'ResourceController@index')->defaults('_config', [
            'repository' => 'Webkul\Attribute\Repositories\AttributeFamilyRepository',
            'resource' => 'Webkul\API\Http\Resources\Catalog\AttributeFamily'
        ]);

        Route::get('families/{id}', 'ResourceController@get')->defaults('_config', [
            'repository' => 'Webkul\Attribute\Repositories\AttributeFamilyRepository',
            'resource' => 'Webkul\API\Http\Resources\Catalog\AttributeFamily'
        ]);

        //Recipe routes
        Route::get('recipes', 'RecipeController@index');
        Route::get('recipes/{slug}', 'RecipeController@getBySlug');

        // Route::get('recipes/{id}', 'RecipeController@get');
        Route::get('/tags', 'TagController@index');

        Route::get('/topics', 'TopicController@index');


        //Product routes
        Route::get('products', 'ProductController@index');

        Route::get('products/{id}', 'ProductController@get');

        Route::get('product-additional-information/{id}', 'ProductController@additionalInformation');

        Route::get('product-configurable-config/{id}', 'ProductController@configurableConfig');

        Route::get('sync-products', 'ProductController@syncProducts');

        //Product Review routes
        Route::get('reviews', 'ResourceController@index')->defaults('_config', [
            'repository' => 'Webkul\Product\Repositories\ProductReviewRepository',
            'resource' => 'Webkul\API\Http\Resources\Catalog\ProductReview'
        ]);

        Route::get('reviews/{id}', 'ResourceController@get')->defaults('_config', [
            'repository' => 'Webkul\Product\Repositories\ProductReviewRepository',
            'resource' => 'Webkul\API\Http\Resources\Catalog\ProductReview'
        ]);

        Route::post('reviews/{id}/create', 'ReviewController@store');

        Route::delete('reviews/{id}', 'ResourceController@destroy')->defaults('_config', [
            'repository' => 'Webkul\Product\Repositories\ProductReviewRepository',
            'resource' => 'Webkul\API\Http\Resources\Catalog\ProductReview',
            'authorization_required' => true
        ]);


        //Channel routes
        Route::get('channels', 'ResourceController@index')->defaults('_config', [
            'repository' => 'Webkul\Core\Repositories\ChannelRepository',
            'resource' => 'Webkul\API\Http\Resources\Core\Channel'
        ]);

        Route::get('channels/{id}', 'ResourceController@get')->defaults('_config', [
            'repository' => 'Webkul\Core\Repositories\ChannelRepository',
            'resource' => 'Webkul\API\Http\Resources\Core\Channel'
        ]);


        //Locale routes
        Route::get('locales', 'ResourceController@index')->defaults('_config', [
            'repository' => 'Webkul\Core\Repositories\LocaleRepository',
            'resource' => 'Webkul\API\Http\Resources\Core\Locale'
        ]);

        Route::get('locales/{id}', 'ResourceController@get')->defaults('_config', [
            'repository' => 'Webkul\Core\Repositories\LocaleRepository',
            'resource' => 'Webkul\API\Http\Resources\Core\Locale'
        ]);


        //Country routes
        Route::get('countries', 'ResourceController@index')->defaults('_config', [
            'repository' => 'Webkul\Core\Repositories\CountryRepository',
            'resource' => 'Webkul\API\Http\Resources\Core\Country'
        ]);

        Route::get('countries/{id}', 'ResourceController@get')->defaults('_config', [
            'repository' => 'Webkul\Core\Repositories\CountryRepository',
            'resource' => 'Webkul\API\Http\Resources\Core\Country'
        ]);

        Route::get('country-states', 'CoreController@getCountryStateGroup');


        //Slider routes
        Route::get('sliders', 'ResourceController@index')->defaults('_config', [
            'repository' => 'Webkul\Core\Repositories\SliderRepository',
            'resource' => 'Webkul\API\Http\Resources\Core\Slider'
        ]);

        Route::get('sliders/{id}', 'ResourceController@get')->defaults('_config', [
            'repository' => 'Webkul\Core\Repositories\SliderRepository',
            'resource' => 'Webkul\API\Http\Resources\Core\Slider'
        ]);


        //Currency routes
        Route::get('currencies', 'ResourceController@index')->defaults('_config', [
            'repository' => 'Webkul\Core\Repositories\CurrencyRepository',
            'resource' => 'Webkul\API\Http\Resources\Core\Currency'
        ]);

        Route::get('currencies/{id}', 'ResourceController@get')->defaults('_config', [
            'repository' => 'Webkul\Core\Repositories\CurrencyRepository',
            'resource' => 'Webkul\API\Http\Resources\Core\Currency'
        ]);

        Route::get('config', 'CoreController@getConfig');


        //Customer routes
        Route::get('get-products', 'CustomerController@getProducts');
        Route::post('customer/login', 'SessionController@create');

        Route::post('customer/forgot-password', 'ForgotPasswordController@store');

        Route::get('customer/logout', 'SessionController@destroy');

        Route::get('customer/get', 'SessionController@get');

        Route::put('customer/profile', 'SessionController@update');

        Route::delete('customer', 'CustomerController@delete');

        Route::post('customer/register', 'CustomerController@create');

        Route::get('customer/generate-qr', 'CustomerController@generateQRCode');

        Route::post('customer/validate-qr', 'CustomerController@verifyQRCode');

        Route::put('/favorite-products/{id}', 'CustomerController@favoriteProduct');

        Route::put('/favorite-recipes/{id}', 'CustomerController@favoriteRecipe');

        Route::get('/favorite-products', 'CustomerController@getFavoriteProducts');
        
        Route::get('/favorite-recipes', 'CustomerController@getFavoriteRecipes');

        Route::get('customers/{id}', 'CustomerController@get')->defaults('_config', [
            'repository' => 'Webkul\Customer\Repositories\CustomerRepository',
            'resource' => 'Webkul\API\Http\Resources\Customer\Customer',
            'authorization_required' => true
        ]);


        //Customer Address routes
        Route::get('addresses', 'AddressController@get')->defaults('_config', [
            'authorization_required' => true
        ]);

        Route::get('addresses/{id}', 'ResourceController@get')->defaults('_config', [
            'repository' => 'Webkul\Customer\Repositories\CustomerAddressRepository',
            'resource' => 'Webkul\API\Http\Resources\Customer\CustomerAddress',
            'authorization_required' => true
        ]);

        Route::delete('addresses/{id}', 'ResourceController@destroy')->defaults('_config', [
            'repository' => 'Webkul\Customer\Repositories\CustomerAddressRepository',
            'resource' => 'Webkul\API\Http\Resources\Customer\CustomerAddress',
            'authorization_required' => true
        ]);

        Route::post('addresses/default/{id}', 'AddressController@makeDefault')->defaults('_config', [
            'authorization_required' => true
        ]);

        Route::put('addresses/{id}', 'AddressController@update')->defaults('_config', [
            'authorization_required' => true
        ]);

        Route::post('addresses/create', 'AddressController@store')->defaults('_config', [
            'authorization_required' => true
        ]);


        //Order routes
        Route::get('orders', 'ResourceController@index')->defaults('_config', [
            'repository' => 'Webkul\Sales\Repositories\OrderRepository',
            'resource' => 'Webkul\API\Http\Resources\Sales\Order',
            'authorization_required' => true
        ]);

        Route::get('orders/{id}', 'ResourceController@get')->defaults('_config', [
            'repository' => 'Webkul\Sales\Repositories\OrderRepository',
            'resource' => 'Webkul\API\Http\Resources\Sales\Order',
            'authorization_required' => true
        ]);

        Route::get('order-email', function(){
            $order = Order::find(1);
            if (!is_null($order)) {
                Mail::to($order->customer_email)->send(new OrderReceipt($order));
            }
         });


        Route::get('orders/{id}/download-v2', function(){
            $order = Order::find(1);
            return view('shop::emails.sales.order-receipt', compact('order'))->render();
         });

        Route::get('orders/{id}/download', 'CustomerController@downloadReciept')->defaults('_config', [
            'repository' => 'Webkul\Sales\Repositories\OrderRepository',
            // 'authorization_required' => true
        ]);


        //Invoice routes
        Route::get('invoices', 'InvoiceController@index')->defaults('_config', [
            'repository' => 'Webkul\Sales\Repositories\InvoiceRepository',
            'resource' => 'Webkul\API\Http\Resources\Sales\Invoice',
            'authorization_required' => true
        ]);

        Route::get('invoices/{id}', 'InvoiceController@get')->defaults('_config', [
            'repository' => 'Webkul\Sales\Repositories\InvoiceRepository',
            'resource' => 'Webkul\API\Http\Resources\Sales\Invoice',
            'authorization_required' => true
        ]);


        //Shipment routes
        Route::get('shipments', 'ResourceController@index')->defaults('_config', [
            'repository' => 'Webkul\Sales\Repositories\ShipmentRepository',
            'resource' => 'Webkul\API\Http\Resources\Sales\Shipment',
            'authorization_required' => true
        ]);

        Route::get('shipments/{id}', 'ResourceController@get')->defaults('_config', [
            'repository' => 'Webkul\Sales\Repositories\ShipmentRepository',
            'resource' => 'Webkul\API\Http\Resources\Sales\Shipment',
            'authorization_required' => true
        ]);

        //Transaction routes
        Route::get('transactions', 'TransactionController@index')->defaults('_config', [
            'repository' => 'Webkul\Sales\Repositories\OrderTransactionRepository',
            'resource' => 'Webkul\API\Http\Resources\Sales\OrderTransaction',
            'authorization_required' => true
        ]);

        Route::get('transactions/{id}', 'TransactionController@get')->defaults('_config', [
            'repository' => 'Webkul\Sales\Repositories\OrderTransactionRepository',
            'resource' => 'Webkul\API\Http\Resources\Sales\OrderTransaction',
            'authorization_required' => true
        ]);

        //Wishlist routes
        Route::get('wishlist', 'ResourceController@index')->defaults('_config', [
            'repository' => 'Webkul\Customer\Repositories\WishlistRepository',
            'resource' => 'Webkul\API\Http\Resources\Customer\Wishlist',
            'authorization_required' => true
        ]);

        Route::delete('wishlist/{id}', 'ResourceController@destroy')->defaults('_config', [
            'repository' => 'Webkul\Customer\Repositories\WishlistRepository',
            'resource' => 'Webkul\API\Http\Resources\Customer\Wishlist',
            'authorization_required' => true
        ]);

        Route::get('move-to-cart/{id}', 'WishlistController@moveToCart');

        Route::get('wishlist/add/{id}', 'WishlistController@create');

        //Checkout routes
        Route::group(['prefix' => 'checkout'], function ($router) {
            Route::post('cart/add/{id}', 'CartController@store');

            Route::get('cart', 'CartController@get');

            Route::get('cart/empty', 'CartController@destroy');

            Route::put('cart/update', 'CartController@update');

            Route::get('cart/remove-item/{id}', 'CartController@destroyItem');

            Route::post('cart/coupon', 'CartController@applyCoupon');

            Route::delete('cart/coupon', 'CartController@removeCoupon');

            Route::get('cart/move-to-wishlist/{id}', 'CartController@moveToWishlist');

            Route::post('save-address', 'CheckoutController@saveAddress');

            Route::post('save-shipping', 'CheckoutController@saveShipping');

            Route::post('save-payment', 'CheckoutController@savePayment');

            Route::post('check-minimum-order', 'CheckoutController@checkMinimumOrder');

            Route::post('save-order', 'CheckoutController@saveOrder');
        });
    });
});