<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TwoFAConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        
        DB::table('core_config')->updateOrInsert([
            'code'         => 'customer.settings.two_factor_authentication.verification'
        ], [
            'value'        => '0',
            'channel_code' => null,
            'locale_code'  => null,
            'created_at'   => $now,
            'updated_at'   => $now
        ]);
    }
}
