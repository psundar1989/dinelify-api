<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'advance_order_days' => (string) config('dinelify.order_defaults.advance_order_days'),
            'order_cutoff_time' => (string) config('dinelify.order_defaults.order_cutoff_time'),
            'allow_order_edit' => '1',
        ];

        foreach ($defaults as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
