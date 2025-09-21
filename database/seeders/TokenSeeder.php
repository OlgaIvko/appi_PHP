<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TokenType;
use App\Models\ApiService;

class TokenSeeder extends Seeder
{
    public function run()
    {
        // Создаем типы токенов
        $tokenTypes = [
            ['name' => 'Bearer Token', 'slug' => 'bearer', 'description' => 'Bearer token authentication'],
            ['name' => 'API Key', 'slug' => 'api-key', 'description' => 'API key authentication'],
            ['name' => 'Basic Auth', 'slug' => 'basic', 'description' => 'Basic authentication with username:password'],
        ];

        foreach ($tokenTypes as $type) {
            TokenType::create($type);
        }

        // Привязываем типы токенов к сервисам
        $wbService = ApiService::where('name', 'Wildberries')->first();
        $ozonService = ApiService::where('name', 'Ozon')->first();

        if ($wbService) {
            $wbService->tokenTypes()->attach(
                TokenType::whereIn('slug', ['bearer', 'api-key'])->pluck('id')
            );
        }

        if ($ozonService) {
            $ozonService->tokenTypes()->attach(
                TokenType::whereIn('slug', ['bearer', 'api-key', 'basic'])->pluck('id')
            );
        }
    }
}
