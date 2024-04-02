<?php

namespace App\Http\Connectors;

use Illuminate\Support\Facades\Log;
use Webkul\Attribute\Models\Attribute;
use Webkul\Category\Models\Category;
use Webkul\Category\Models\CategoryTranslation;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductAttributeValue;
use Webkul\Product\Models\ProductFlat;
use Webkul\Product\Models\ProductInventory;
use GuzzleHttp\Client;

class XMLConnector
{

    /**
     * Sync Products using XML Request from Sunbulah Group
     *
     * @param  string  $locale Defines locale whether english or arabic
     * @return void
     */
    public static function syncProducts()
    {
        Log::info("Started: Fetching Products From Sunbulah API");
        ini_set('max_execution_time', -1); //12 minutes
        ini_set("memory_limit", -1);

        $client = new Client();

        $response = $client->post('https://ffpwdprd.ffpgroup.net:44300/sap/bc/srt/rfc/sap/zws_ecomm/200/zws_ecomm_srv/zws_ecomm_srv_bn', [
            'headers' => [
                'Content-Type' => 'text/xml',
                'SOAPAction' => '"#POST"',
                'Authorization' => 'Basic RUNPTVNFUjpBYmNkQDEyMzQ='
            ],
            'body' => "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:urn=\"urn:sap-com:document:sap:rfc:functions\">\n   <soapenv:Header/>\n   <soapenv:Body>\n      <urn:ZIF_ECOM_MATETAIL_MASTER/>\n   </soapenv:Body>\n</soapenv:Envelope>"
        ]);

        $result = $response->getBody()->getContents();

        // <MATNR>000000000050476118</MATNR> // SKU
        // <MAKTX>SUNB FRENCH FRIES 10MM-F-(10X1KG) RETAIL</MAKTX> // Name (EN)
        // <MAKTX_AR>بطاطس السنبله للقلي10 ملم(10*1كجم)تجزئة</MAKTX_AR> // Name (AR)
        // <KDMAT/>
        // <MATKL>TFF</MATKL>
        // <DESCR>SUNB FRENCH FRIES 10MM-F-(10X1KG) RETAIL</DESCR>
        // <NTGEW>10.0</NTGEW>
        // <GEWEI>KG</GEWEI>
        // <PRICE>100.0</PRICE>
        // <WAERS>SAR</WAERS>
        // <MVGR2>FF</MVGR2>
        // <BEZEI2>French Fries</BEZEI2> // Category
        // <MVGR4>SUN</MVGR4>
        // <BEZEI4>Sunbulah</BEZEI4>
        // <LABST>13253.9</LABST>
        // <MEINS>CS</MEINS>

        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->loadXML($result);
        $XMLresults = $doc->getElementsByTagName("item");
        $XMLresults->length;

        $filtered_products = [];
        $uniqueNames = [];
        $configurable_names = [];

        for ($i = 0; $i < $XMLresults->length; $i++) {
            $product_node = $XMLresults->item($i);
            $brand = XMLConnector::getProductAttribute($product_node, "BEZEI4");
            if ($brand != "Sunbulah") continue;
            $product = [
                "sku" => XMLConnector::getProductAttribute($product_node, "MATNR"),
                "name" => XMLConnector::getProductAttribute($product_node, "MAKTX"),
                "name_ar" => XMLConnector::getProductAttribute($product_node, "MAKTX_AR"),
                "desc" => XMLConnector::getProductAttribute($product_node, "DESCR"),
                "category" => XMLConnector::getProductAttribute($product_node, "BEZEI2"),
                "weight" => XMLConnector::getProductAttribute($product_node, "NTGEW"),
                "unit" => XMLConnector::getProductAttribute($product_node, "GEWEI"),
                "price" => XMLConnector::getProductAttribute($product_node, "PRICE"),
                "brand" => XMLConnector::getProductAttribute($product_node, "BEZEI4"),
                "quantity" => XMLConnector::getProductAttribute($product_node, "LABST")
            ];

            $splitted_name = explode('(', $product["name"]);

            $splitted_name = rtrim($splitted_name[0]," ");

            if (in_array($splitted_name, $uniqueNames)) {
                if (!in_array($splitted_name, $configurable_names))
                    array_push($configurable_names, $splitted_name);
            } else {
                array_push($uniqueNames, $splitted_name);
            }

            array_push($filtered_products, $product);
        }

        usort($filtered_products, function($a, $b)
        {
            return strcmp($a["name"], $b["name"]);
        });

        $configurable_result_products = [];

        foreach ($filtered_products as $_product) {

            $category = $_product["category"];
            $category_slug = str_replace(' ', '-', strtolower($category));
            $category = Category::updateOrCreate(
                ["strapi_slug" => $category_slug],
                [
                    "position" => 1,
                    "status" => 1,
                    "display_mode" => "products_and_description"
                ]
            );

            CategoryTranslation::updateOrCreate([
                "category_id" => $category->id,
                "locale" => "en"
            ], [
                "name" => $category,
                "description" => $category,
                "slug" => $category_slug
            ]);

            $splitted_name = explode('(', $_product["name"]);

            $splitted_name = rtrim($splitted_name[0]," ");


            $product_slug = str_replace(' ', '-', strtolower($splitted_name));


            $type = "simple";
            $parent_product_id = null;
            $parent_pf_id = null;
            $parent_pf_ar_id = null;

            // Is configurable (has variants)
            if (in_array($splitted_name, $configurable_names)) {
                if (in_array($splitted_name, $configurable_result_products)) { // Is configurable (has variants) and is a variant

                    // Find parent
                    $p = Product::firstWhere([
                        "type" => "configurable",
                        "slug" => $product_slug
                    ]);
                    $pf = ProductFlat::firstWhere([
                        "product_id" => $p->id,
                        "locale" => "en"
                    ]);
    
                    $pf_ar = ProductFlat::firstWhere([
                        "product_id" => $p->id,
                        "locale" => "ar"
                    ]);
                    $parent_product_id = $p->id;
                    $parent_pf_id = $pf->id;
                    $parent_pf_ar_id = $pf_ar->id;
                    
                } else { // is a parent
                    $type = "configurable";
                    array_push($configurable_result_products, $splitted_name);
                }

            }
             

            $sku = $_product["sku"];
            $product = Product::updateOrCreate(
                ["sku" => $sku],
                [
                    "type" => $type,
                    "attribute_family_id" => 1,
                    "slug" => $product_slug,
                    "parent_id" => $parent_product_id
                ]
            );
            if ($product->wasRecentlyCreated) {
                $product->sku = (string) $sku;
                $product->save();
            }

            // ATTRIBUTES 
            $attributes = ["name", "description", "short_description", "sku", "product_number", "price", "weight"];
            $attributes_values = Attribute::whereIn("code", $attributes)->get();
            foreach ($attributes as $attribute) {
                $attr_value = $attributes_values->firstWhere("code", $attribute);
                $attr_data_en = [
                    "channel" => "default",
                    "product_id" => $product->id,
                    "attribute_id" => $attr_value->id
                ];

                
                $value = '';
                $value_ar = '';
                switch ($attribute) {
                    case 'name':
                        $value = $_product["name"];
                        $value_ar = $_product["name_ar"];
                        break;
                    case 'description':
                        $value = $_product["desc"];
                        break;
                    case 'short_description':
                        $value = $_product["desc"];
                        break;
                    case 'sku':
                        $value = $_product["sku"];
                        break;
                    case 'product_number':
                        $value = $_product["sku"];
                        break;
                    case 'price':
                        $value = $_product["price"];
                        break;
                    case 'weight':
                        $value = $_product["weight"];
                        break;
                    default:
                        break;
                }
                ;
                $attr_data_en["text_value"] = $value;
                if (!($value_ar === null || trim($value_ar) === '')) {
                    $attr_data_ar = [
                        "channel" => "default",
                        "product_id" => $product->id,
                        "attribute_id" => $attr_value->id,
                        "text_value" => $value_ar
                    ];
                    ProductAttributeValue::updateOrCreate([
                        "product_id" => $product->id,
                        "attribute_id" => $attr_value->id,
                        "locale" => "ar"
                    ], $attr_data_ar);
                }

                ProductAttributeValue::updateOrCreate([
                    "product_id" => $product->id,
                    "attribute_id" => $attr_value->id,
                    "locale" => "en"
                ], $attr_data_en);


            }

            $product->categories()->sync([$category->id]);

            // UPDATE PRODUCT INVENTORY
            $productInventory = ProductInventory::updateOrCreate(
                ["product_id" => $product->id],
                [
                    'inventory_source_id' => 1,
                    'vendor_id' => 0 // TO BE REPLACED BY VENDORS
                ]
            );

            if ($productInventory->wasRecentlyCreated) {
                $productInventory->qty = isset($_product["quantity"]) ? floor($_product["quantity"]) : 100;
                $productInventory->save();
            }

            $productFlat = ProductFlat::updateOrCreate(
                [
                    'sku' => $sku,
                    "locale" => "en"
                ],
                [
                    "product_id" => $product->id,
                    'name' => $_product["name"],
                    "description" => $_product["desc"],
                    "price" => $_product["price"],
                    "weight" => $_product["weight"],
                    "weight_label" => $_product["weight"] . " " . $_product["unit"],
                    "new" => 1,
                    'status' => 1,
                    "channel" => "default",
                    "visible_individually" => 1,
                    "parent_id" => $parent_pf_id
                ]
            );

            $productFlatAr = ProductFlat::updateOrCreate(
                [
                    'sku' => $sku,
                    "locale" => "ar"
                ],
                [
                    "product_id" => $product->id,
                    'name' => $_product["name_ar"],
                    "description" => $_product["desc"],
                    "price" => $_product["price"],
                    "weight" => $_product["weight"],
                    "weight_label" => $_product["weight"] . " " . $_product["unit"],
                    "new" => 1,
                    'status' => 1,
                    "channel" => "default",
                    "visible_individually" => 1,
                    "parent_id" => $parent_pf_ar_id
                ]
            );
        }
    }


    /**
     * Get Product attribute from XML Node
     *
     * @param  $product Product Element
     * @param  $attr_name Attribute Name
     * @return void
     */
    public static function getProductAttribute($product, $attr_name): string
    {
        $node = $product->getElementsByTagName($attr_name);
        return $node->item(0)->nodeValue;
    }


}