<?php

namespace App\Http\Connectors;

use Illuminate\Support\Facades\Http;
use Webkul\Attribute\Models\Attribute;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductInventory;
use Webkul\Product\Models\ProductFlat;
use Webkul\Product\Models\ProductAttributeValue;
use Webkul\Category\Models\Category;
use Webkul\Category\Models\CategoryProxy;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class StrapiConnector {
    
    
    public static function syncProducts(string $locale = "en") {
        $base_url = env('STRAPI_URL');

        Log::info("Started: Fetching Products");

        $endpoint = "/categories?_locale=$locale";

        $response = Http::get($base_url . $endpoint);

        $results = $response->json();

        foreach ($results as $strapi_category) {
            StrapiConnector::updateProduct($strapi_category);
        }
    }

    public static function updateProduct($strapi_category)
    {
        ini_set('max_execution_time', 180); //3 minutes
        $category = Category::updateOrCreate(["strapi_slug" => $strapi_category["slug"]],
        [
            "position" => 1,
            "status" => 1,
            "display_mode" => "products_and_description"
        ]);

        foreach ($strapi_category["products"] as $strapi_product) {
            $product = Product::updateOrCreate(["slug" => $strapi_product["slug"]],
            [
                "type" => "simple",
                "attribute_family_id" => 1
            ]);
            if ($product->wasRecentlyCreated) {
                $product->sku = (string) substr(Str::uuid(), 0, 7);
                $product->save();
            }

            // ATTRIBUTES 
            $attributes = ["name", "description", "short_description", "sku", "product_number"];
            $attributes_values = Attribute::whereIn("code", $attributes)->get();
            foreach ($attributes as $attribute) {
                $attr_value = $attributes_values->firstWhere("code", $attribute);
                $attr_data = [
                    "locale" => $strapi_product["locale"],
                    "channel" => "default",
                    "product_id" => $product->id,
                    "attribute_id" => $attr_value->id
                ];
                switch ($attribute) {
                    case 'name':
                        $attr_data["text_value"] = $strapi_product["name"];
                        break;
                    case 'description':
                        $attr_data["text_value"] = $strapi_product["description"];
                        break;
                    case 'short_description':
                        $attr_data["text_value"] = $strapi_product["description"];
                        break;
                    case 'sku':
                        $attr_data["text_value"] = $product->sku;
                        break;
                    case 'product_number':
                        $attr_data["text_value"] = $product->sku;
                        break;
                    default:
                        break;
                }
                ProductAttributeValue::updateOrCreate([
                    "product_id" => $product->id,
                    "attribute_id" => $attr_value->id
                ], $attr_data);
            }

            $product->categories()->sync([$category->id]);

            // UPDATE PRODUCT INVENTORY
            $productInventory = ProductInventory::updateOrCreate(["product_id" => $product->id],
            [
                'inventory_source_id' => 1,
                'vendor_id' => 0 // TO BE REPLACED BY VENDORS
            ]);

            if ($productInventory->wasRecentlyCreated) {
                $productInventory->qty = 100;
                $productInventory->save();
            }

            $productFlat = ProductFlat::updateOrCreate(["product_id" => $product->id],
            [
                'sku' => $product->sku,
                'name' => $strapi_product["name"],
                "description" => $strapi_product["description"],
                "new" => 1,
                'status' => 1,
                "channel" => "default",
                "visible_individually" => 1,
                "locale"=> $strapi_product["locale"],
                "thumbnail" => count($strapi_product["desktop_images"]) > 0 ? $strapi_product["desktop_images"][0]["url"] : null
            ]);
        }
    }
}