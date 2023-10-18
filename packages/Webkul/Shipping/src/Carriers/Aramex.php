<?php

namespace Webkul\Shipping\Carriers;

use Aramex as AramexSDK;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Models\CartShippingRate;
use Webkul\Shipping\Carriers\AbstractShipping;

class Aramex extends AbstractShipping
{
    /**
     * Shipping method code
     *
     * @var string
     */
    protected $code  = 'aramex';

    /**
     * Returns rate for shipping method
     *
     * @return CartShippingRate|false
     */
    public function calculate()
    {
        if (! $this->isAvailable()) {
            return false;
        }

        $cart = Cart::getCart();
        
        $object = new CartShippingRate;

        $object->carrier = 'aramex';
        $object->carrier_title = $this->getConfigData('title');
        $object->method = 'aramex_aramex';
        $object->method_title = $this->getConfigData('title');
        $object->method_description = $this->getConfigData('description');
        $object->price = 0;
        $object->base_price = 0;

        if ($this->getConfigData('type') == 'per_unit') {
            $shippingAddress = $cart->shipping_address;
            $originAddress = [
                'line1' => 'Industrial City',
                'city' => 'Jeddah',
                'country_code' => 'SA'
            ];
    
            $destinationAddress = [
                'line1' => $shippingAddress["address1"],
                'city' => $shippingAddress["city"],
                'country_code' => 'EG'
            ];

            $totalWeight = 0;

            foreach ($cart->items as $item) {
                if ($item->product->getTypeInstance()->isStockable()) {
                    $totalWeight += $item->total_weight;
                }
            }

            $shipmentDetails = [
                'weight' => $totalWeight, // KG
                'number_of_pieces' => $cart->items_qty
            ];
    
            $data = AramexSDK::calculateRate($originAddress, $destinationAddress , $shipmentDetails , 'SAR');
            
            if((isset($data->HasErrors) && $data->HasErrors) || isset($data->errors)){
              logger([
                "data" => $data,
                "message" => "Failed to Calculate ARAMEX rates"
              ]);
              $object->price = core()->convertPrice($this->getConfigData('default_rate'));
              $object->base_price = $this->getConfigData('default_rate');
              return $object;
            }
            
            $object->price = $data->TotalAmount->Value;
            $object->base_price = $data->TotalAmount->Value;
            
        } else {
            $object->price = core()->convertPrice($this->getConfigData('default_rate'));
            $object->base_price = $this->getConfigData('default_rate');
        }

        return $object;
    }
}