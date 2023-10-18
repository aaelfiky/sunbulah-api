<?php

namespace App\Http\Connectors;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Webkul\Attribute\Models\Attribute;
use Webkul\Category\Models\Category;
use Webkul\Category\Models\CategoryTranslation;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Customer\Models\UserProduct;
use Webkul\Customer\Models\UserRecipe;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductAttributeValue;
use Webkul\Product\Models\ProductFlat;
use Webkul\Product\Models\ProductImageProxy;
use Webkul\Product\Models\ProductInventory;

class StrapiConnector {
    
    /**
    * Sync Products with Strapi to DB
    *
    * @param  string  $locale Defines locale whether english or arabic
    * @return void
    */
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

    /**
    * Takes a strapi product category as an input and updates this category and its child products
    *
    * @param  object  $strapi_category Strapi Category
    * @return void
    */
    public static function updateProduct($strapi_category)
    {
        ini_set('max_execution_time', 180); //3 minutes
        $category = Category::updateOrCreate(["strapi_slug" => $strapi_category["slug"]],
        [
            "position" => 1,
            "status" => 1,
            "display_mode" => "products_and_description"
        ]);

        CategoryTranslation::updateOrCreate([
            "category_id" => $category->id,
            "locale" => $strapi_category["locale"]
        ],[
            "name" => $strapi_category["name"],
            "description" => $strapi_category["description"],
            "slug" => $strapi_category["slug"]
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

            $productFlat = ProductFlat::updateOrCreate([
                "product_id" => $product->id,
                "locale"=> $strapi_product["locale"]
            ],
            [
                'sku' => $product->sku,
                'name' => $strapi_product["name"],
                "description" => $strapi_product["description"],
                "new" => 1,
                'status' => 1,
                "channel" => "default",
                "visible_individually" => 1,
                "thumbnail" => count($strapi_product["desktop_images"]) > 0 ? $strapi_product["desktop_images"][0]["url"] : null
            ]);

            foreach ($strapi_product["desktop_images"] as $image) {
                ProductImageProxy::updateOrCreate([
                    "product_id" => $product->id,
                    "path" => $image["url"]
                ], ["type" => "png"]);
            }
        }
    }


    /**
    * Syncs strapi users (executed once)
    *
    * @param  object  $strapi_category Strapi Category
    * @return void
    */
    public static function syncUsers()
    {
        ini_set('max_execution_time', 180); //3 minutes

        $base_url = env('STRAPI_URL');

        Log::info("Started: Syncing Users");

        $endpoint = "/users";

        $response = Http::get($base_url . $endpoint);

        $results = $response->json();

        foreach ($results as $strapi_user) {
            
            $customer = Customer::updateOrCreate(["email" => $strapi_user["email"]],
            [
                "first_name" => $strapi_user["username"],
                "last_name" => $strapi_user["username"],
                "created_at" => $strapi_user["created_at"],
                "customer_group_id" => CustomerGroup::GENERAL,
                "updated_at" => $strapi_user["updated_at"]
            ]);

            foreach ($strapi_user["favoriteRecipes"] as $key => $recipe) {
                UserRecipe::updateOrCreate([
                    "customer_id" => $customer->id,
                    "recipe_id" => $recipe["id"],
                ], []);
            }

            foreach ($strapi_user["favoriteProducts"] as $key => $product) {
                $bagisto_product = Product::firstWhere("slug", $product["slug"]);
                if (!is_null($bagisto_product)) {
                    UserProduct::updateOrCreate([
                        "customer_id" => $customer->id,
                        "product_id" => $bagisto_product["id"],
                    ], []);
                }
            }
        }
        
    }

    public static function getFavoriteRecipes($query): array
    {
        ini_set('max_execution_time', 180); //3 minutes

        $base_url = env('STRAPI_URL');

        Log::info("Started: Fetching Favorite Recipes");

        $endpoint = "/recipes";

        $response = Http::get($base_url . $endpoint, $query);

        $results = $response->json();

        return $results;
    }
}