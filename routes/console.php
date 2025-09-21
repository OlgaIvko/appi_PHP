<?php

use Illuminate\Support\Facades\Artisan;
use App\Services\WbApiService;

Artisan::command('test:debug-simple', function () {
    $this->info('Simple debug test command');

    // Простой тест сервиса
    try {
        $service = new WbApiService();
        $this->info('WbApiService loaded successfully');

        // Тестируем публичные методы
        $products = $service->getMockProducts();
        $this->info('Mock products: ' . count($products));
    } catch (Exception $e) {
        $this->error('Error: ' . $e->getMessage());
    }
})->describe('Simple debug test');
