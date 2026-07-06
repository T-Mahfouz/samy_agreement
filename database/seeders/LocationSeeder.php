<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Region;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            'منطقة الرياض' => ['الرياض', 'الدرعية', 'الخرج'],
            'منطقة مكة المكرمة' => ['جدة', 'مكة المكرمة', 'الطائف', 'القنفذة', 'الليث'],
            'منطقة المدينة المنورة' => ['المدينة المنورة', 'ينبع', 'العلا'],
            'المنطقة الشرقية' => ['الدمام', 'الخبر', 'الأحساء', 'الجبيل'],
            'منطقة عسير' => ['أبها', 'خميس مشيط'],
        ];

        foreach ($regions as $regionName => $cities) {
            $region = Region::create(['name' => $regionName, 'is_active' => true]);

            foreach ($cities as $cityName) {
                City::create([
                    'region_id' => $region->id,
                    'name' => $cityName,
                    'is_active' => true,
                ]);
            }
        }
    }
}
