<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ApiService;

class ApiServicesSeeder extends Seeder
{
    public function run()
    {
        $services = [
            [
                'name' => 'Wildberries',
                'base_url' => 'https://suppliers-api.wildberries.ru',
                'description' => 'Wildberries API',
                'is_active' => true,
            ],
            [
                'name' => 'Ozon',
                'base_url' => 'https://api-seller.ozon.ru',
                'description' => 'Ozon API',
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            ApiService::create($service);
        }
    }
}
