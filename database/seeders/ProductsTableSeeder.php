<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeOption;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attribute = Attribute::firstWhere([
            "code" => 'weight'
        ]);
        
        $attribute->update([
            "type" => 'select',
            "is_configurable" => '1',
            "is_user_defined" => '1',
            "swatch_type" => 'dropdown',
            "use_in_flat" => '1'
        ]);

        $options = ["250g", "500g", "750g", "1kg", "1.5kg", "2kg"];
        for ($i=0; $i < count($options); $i++) { 
            AttributeOption::updateOrCreate([
                "admin_name" => $options[$i],
                "attribute_id" => $attribute->id
            ], [
                "sort_order" => $i + 1
            ]);
        }

        Attribute::where([
            "code" => 'size'
        ])->orWhere([
            "code" => 'color'
        ])->update([
            "is_configurable" => '0'
        ]);
    }
}
