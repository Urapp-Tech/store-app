<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayfastSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        DB::table('addon_settings')->updateOrInsert(['key_name' => 'payfast', 'settings_type' => 'payment_config'], [
            'key_name' => 'payfast',
            'live_values' => '{"gateway":"payfast","mode":"live","status":"0","merchant_id": null, "secured_key": null}',
            'test_values' => '{"gateway":"payfast","mode":"test","status":"0","merchant_id": null, "secured_key": null}',
            'settings_type' => 'payment_config',
            'mode' =>  'live',
            "id" => Str::uuid(),
            'is_active' =>  0,
            'additional_data' => '{"gateway_title":"PayFast","gateway_image":null}',
        ]);
    }
}
