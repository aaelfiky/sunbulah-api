<?php

namespace Webkul\API\Http\Controllers\Shop;

use Aramex;
use Cart;
use Exception;
use Illuminate\Support\Str;
use Webkul\Payment\Facades\Payment;
use Webkul\Shipping\Facades\Shipping;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Shop\Http\Controllers\OnepageController;
use Webkul\Checkout\Repositories\CartItemRepository;
use Webkul\Checkout\Http\Requests\CustomerAddressForm;
use Webkul\API\Http\Resources\Sales\Order as OrderResource;
use Webkul\API\Http\Resources\Checkout\Cart as CartResource;
use Webkul\API\Http\Resources\Checkout\CartShippingRate as CartShippingRateResource;

class CheckoutController extends Controller
{
    /**
     * Contains current guard
     *
     * @var array
     */
    protected $guard;

    /**
     * CartRepository object
     *
     * @var \Webkul\Checkout\Repositories\CartRepository
     */
    protected $cartRepository;

    /**
     * CartItemRepository object
     *
     * @var \Webkul\Checkout\Repositories\CartItemRepository
     */
    protected $cartItemRepository;

    /**
     * Controller instance
     *
     * @param  \Webkul\Checkout\Repositories\CartRepository  $cartRepository
     * @param  \Webkul\Checkout\Repositories\CartItemRepository  $cartItemRepository
     * @param  \Webkul\Sales\Repositories\OrderRepository  $orderRepository
     */
    public function __construct(
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        OrderRepository $orderRepository
    )
    {
        $this->guard = request()->has('token') ? 'api' : 'customer';

        auth()->setDefaultDriver($this->guard);

        // $this->middleware('auth:' . $this->guard);

        $this->_config = request('_config');

        $this->cartRepository = $cartRepository;

        $this->cartItemRepository = $cartItemRepository;

        $this->orderRepository = $orderRepository;
    }

    /**
     * Saves customer address.
     *
     * @param  \Webkul\Checkout\Http\Requests\CustomerAddressForm $request
     * @return \Illuminate\Http\Response
    */
    public function saveAddress(CustomerAddressForm $request)
    {
        $data = request()->all();

        if ($data['billing']['use_for_shipping']) {
            $data['shipping'] = $data["billing"];
            unset($data["shipping"]['use_for_shipping']);
        }

        $data['billing']['address1'] = implode(PHP_EOL, array_filter($data['billing']['address1']));

        $data['shipping']['address1'] = implode(PHP_EOL, array_filter($data['shipping']['address1']));

        if (isset($data['billing']['id']) && str_contains($data['billing']['id'], 'address_')) {
            unset($data['billing']['id']);
            unset($data['billing']['address_id']);
        }

        if (isset($data['shipping']['id']) && Str::contains($data['shipping']['id'], 'address_')) {
            unset($data['shipping']['id']);
            unset($data['shipping']['address_id']);
        }


        if (Cart::hasError() || ! Cart::saveCustomerAddress($data) || ! Shipping::collectRates()) {
            abort(400);
        }

        $rates = [];

        foreach (Shipping::getGroupedAllShippingRates() as $code => $shippingMethod) {
            $rates[] = [
                'carrier_title' => $shippingMethod['carrier_title'],
                'rates'         => CartShippingRateResource::collection(collect($shippingMethod['rates'])),
            ];
        }

        Cart::collectTotals();

        return response()->json([
            'data' => [
                'rates' => $rates,
                'cart'  => new CartResource(Cart::getCart()),
            ]
        ]);
    }

    /**
     * Saves shipping method.
     *
     * @return \Illuminate\Http\Response
    */
    public function saveShipping()
    {
        $shippingMethod = request()->get('shipping_method');

        if (Cart::hasError()
            || !$shippingMethod
            || ! Cart::saveShippingMethod($shippingMethod)
        ) {
            abort(400);
        }

        Cart::collectTotals();

        return response()->json([
            'data' => [
                'methods' => Payment::getPaymentMethods(),
                'cart'    => new CartResource(Cart::getCart()),
            ]
        ]);
    }

    /**
     * Saves payment method.
     *
     * @return \Illuminate\Http\Response
    */
    public function savePayment()
    {
        $payment = request()->get('payment');

        if (Cart::hasError() || ! $payment || ! Cart::savePaymentMethod($payment)) {
            abort(400);
        }

        return response()->json([
            'data' => [
                'cart' => new CartResource(Cart::getCart()),
            ]
        ]);
    }

    /**
     * Check for minimum order.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkMinimumOrder()
    {
        $minimumOrderAmount = (float) core()->getConfigData('sales.orderSettings.minimum-order.minimum_order_amount') ?? 0;

        $status = Cart::checkMinimumOrder();

        return response()->json([
            'status' => ! $status ? false : true,
            'message' => ! $status ? trans('shop::app.checkout.cart.minimum-order-message', ['amount' => core()->currency($minimumOrderAmount)]) : 'Success',
            'data' => [
                'cart'   => new CartResource(Cart::getCart()),
            ]
        ]);
    }

    /**
     * Saves order.
     *
     * @return \Illuminate\Http\Response
    */
    public function saveOrder()
    {
        if (Cart::hasError()) {
            abort(400);
        }

        Cart::collectTotals();

        $this->validateOrder();

        $cart = Cart::getCart();

        if ($redirectUrl = Payment::getRedirectUrl($cart)) {
            return response()->json([
                    'success'      => true,
                    'redirect_url' => $redirectUrl,
                ]);
        }


        if ($cart->shipping_method == "aramex_aramex") {
            $guid = 0;
            $totalWeight = 0;

            /*
            TODO:: ARAMEX SHIPPING
            foreach ($cart->items as $item) {
                if ($item->product->getTypeInstance()->isStockable()) {
                    $totalWeight += $item->total_weight;
                }
            }

            $data = Aramex::createPickup([
                'name' => $cart->customer_first_name . $cart->customer_last_name,
                'email' => $cart->customer_email,
                'phone'      => optional($cart->customer)->phone ?? "0000000000",
                'cell_phone' =>  optional($cart->customer)->phone ?? "0000000000",
                'country_code' => 'EG',
                'city' => $cart->shipping_address->city,
                'zip_code' => 32160,
                'line1' => $cart->shipping_address->address1,
                'line2' => $cart->shipping_address->city,
                'line3' => $cart->shipping_address->country,
                'pickup_date' => time() + 45000,
                'ready_time' => time()  + 43000,
                'last_pickup_time' => time() +  45000,
                'closing_time' => time()  + 45000,
                'status' => 'Ready', 
                'pickup_location' => $cart->shipping_address->address1,
                'weight' => $totalWeight,
                'volume' => 1
            ]);
    
            // extracting GUID
           if (!$data->error)
              $guid = $data->pickupGUID;

            $callResponse = Aramex::createShipment([
                'shipper' => [
                    'name' => 'Sunbulah',
                    'email' => 'sales@sunbulah.com',
                    'phone'      => '+123456789982',
                    'cell_phone' => '+321654987789',
                    'country_code' => 'SA',
                    'city' => 'Jeddah',
                    'zip_code' => 22752,
                    'line1' => 'Industrial City',
                    'line2' => 'Jeddah',
                    'line3' => 'KSA'
                ],
                'consignee' => [
                    'name' => $cart->customer_first_name . $cart->customer_last_name,
                    'email' => $cart->customer_email,
                    'phone'      => optional($cart->customer)->phone ?? "0000000000",
                    'cell_phone' =>  optional($cart->customer)->phone ?? "0000000000",
                    'country_code' => 'EG',
                    'city' => $cart->shipping_address->city,
                    'zip_code' => 32160,
                    'line1' => $cart->shipping_address->address1,
                    'line2' => $cart->shipping_address->city,
                    'line3' => $cart->shipping_address->country,
                ],
                'shipping_date_time' => time() + 50000,
                'due_date' => time() + 60000,
                'comments' => 'No Comment',
                'pickup_location' => $cart->shipping_address->address1,
                'pickup_guid' => $guid,
                'product_type' => 'EPX',
                'customs_value_amount' => 10,
                'weight' => $totalWeight,
                'customs_value' => 20,
                'number_of_pieces' => $cart->items_qty,
                'description' => 'Sunbulah Shipping',
            ]);

            if (!empty($callResponse->error))
            {
                foreach ($callResponse->errors as $errorObject) {
                  handleError($errorObject->Code, $errorObject->Message);
                }
            }
            else {
              // extract your data here, for example
              // $shipmentId = $response->Shipments->ProcessedShipment->ID;
              // $labelUrl = $response->Shipments->ProcessedShipment->ShipmentLabel->LabelURL;
            }
            */
        }

        $order = $this->orderRepository->create(Cart::prepareDataForOrder());

        Cart::deActivateCart();

        return response()->json([
            'success' => true,
            'order'   => new OrderResource($order),
        ]);
    }

    /**
     * Validate order before creation
     *
     * @throws Exception
     */
    public function validateOrder(): void
    {
        app(OnepageController::class)->validateOrder();
    }
}