<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TokenType;
use App\Models\ApiService;

class TokenTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $tokenTypes = [
            ['name' => 'Bearer Token', 'slug' => 'bearer', 'description' => 'Bearer token authentication'],
            ['name' => 'API Key', 'slug' => 'api-key', 'description' => 'API key authentication'],
            ['name' => 'Basic Auth', 'slug' => 'basic', 'description' => 'Basic authentication with username:password'],
        ];

        foreach ($tokenTypes as $type) {
            \App\Models\TokenType::create($type);
        }

        // Привязываем типы токенов к сервисам
        $wbService = \App\Models\ApiService::where('name', 'Wildberries')->first();
        $ozonService = \App\Models\ApiService::where('name', 'Ozon')->first();

        if ($wbService) {
            $wbService->tokenTypes()->attach(
                \App\Models\TokenType::whereIn('slug', ['bearer', 'api-key'])->pluck('id')
            );
        }

        if ($ozonService) {
            $ozonService->tokenTypes()->attach(
                \App\Models\TokenType::whereIn('slug', ['bearer', 'api-key', 'basic'])->pluck('id')
            );
        }
    }
}
