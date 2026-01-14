<?php

namespace Database\Seeders;

use App\Models\Manufacturer;
use Illuminate\Database\Seeder;

class ManufacturerSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'ABB', 'sort' => 10],
            ['name' => 'KUKA', 'sort' => 20],
            ['name' => 'FANUC', 'sort' => 30],
            ['name' => 'YASKAWA', 'sort' => 40],
            ['name' => 'UR', 'sort' => 50],
            ['name' => 'STAUBLI', 'sort' => 60],
            ['name' => 'OTHER', 'sort' => 999],
        ];

        foreach ($items as $i) {
            Manufacturer::updateOrCreate(['name' => $i['name']], $i + ['is_active' => true]);
        }
    }
}
