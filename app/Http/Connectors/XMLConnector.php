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

        for ($i = 0; $i < $XMLresults->length; $i++) {

            $product_node = $XMLresults->item($i);
            $category = XMLConnector::getProductAttribute($product_node, "BEZEI2");
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

            $sku = XMLConnector::getProductAttribute($product_node, "MATNR");
            $product = Product::updateOrCreate(
                ["sku" => $sku],
                [
                    "type" => "simple",
                    "attribute_family_id" => 1,
                    "slug" => $category_slug . $sku
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

                $tag_name = '';
                $tag_name_ar = '';
                switch ($attribute) {
                    case 'name':
                        $tag_name = 'MAKTX';
                        $tag_name_ar = 'MAKTX_AR';
                        break;
                    case 'description':
                        $tag_name = 'DESCR';
                        break;
                    case 'short_description':
                        $tag_name = 'DESCR';
                        break;
                    case 'sku':
                        $tag_name = 'MATNR';
                        break;
                    case 'product_number':
                        $tag_name = 'MATNR';
                        break;
                    case 'price':
                        $tag_name = 'PRICE';
                        break;
                    case 'weight':
                        $tag_name = 'NTGEW';
                        break;
                    default:
                        break;
                }
                ;
                $attr_data_en["text_value"] = XMLConnector::getProductAttribute($product_node, $tag_name);
                if (!($tag_name_ar === null || trim($tag_name_ar) === '')) {
                    $attr_data_ar = [
                        "channel" => "default",
                        "product_id" => $product->id,
                        "attribute_id" => $attr_value->id,
                        "text_value" => XMLConnector::getProductAttribute($product_node, $tag_name_ar)
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
                $productInventory->qty = 100; // TO BE REPLACED WITH KDMAT
                $productInventory->save();
            }

            $productFlat = ProductFlat::updateOrCreate(
                [
                    "product_id" => $product->id,
                    "locale" => "en"
                ],
                [
                    'sku' => $sku,
                    'name' => XMLConnector::getProductAttribute($product_node, "MAKTX"),
                    "description" => XMLConnector::getProductAttribute($product_node, "DESCR"),
                    "price" => XMLConnector::getProductAttribute($product_node, "PRICE"),
                    "weight" => XMLConnector::getProductAttribute($product_node, "NTGEW"),
                    "new" => 1,
                    'status' => 1,
                    "channel" => "default",
                    "visible_individually" => 1
                ]
            );

            $productFlatAr = ProductFlat::updateOrCreate(
                [
                    "product_id" => $product->id,
                    "locale" => "ar"
                ],
                [
                    'sku' => $sku,
                    'name' => XMLConnector::getProductAttribute($product_node, "MAKTX_AR"),
                    "description" => XMLConnector::getProductAttribute($product_node, "DESCR"),
                    "price" => XMLConnector::getProductAttribute($product_node, "PRICE"),
                    "weight" => XMLConnector::getProductAttribute($product_node, "NTGEW"),
                    "new" => 1,
                    'status' => 1,
                    "channel" => "default",
                    "visible_individually" => 1
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