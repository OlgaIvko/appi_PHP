<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WbApiService;
use ReflectionClass;
use ReflectionMethod;

class TestDebugCommand extends Command
{
    protected $signature = 'tdebug:test';
    protected $description = 'Test debug console output from WbApiService';

    public function handle()
    {
        $this->info('=== TESTING WB API SERVICE DEBUG OUTPUT ===');

        $apiService = new WbApiService();

        // Включаем debug режим через публичный метод
        $apiService->setDebug(true);

        $this->info('1. Testing public methods...');

        // Тестируем публичные методы
        $products = $apiService->getMockProducts();
        $this->info("   Mock products: " . count($products));

        $stocks = $apiService->getMockStocks();
        $this->info("   Mock stocks: " . count($stocks));

        $this->info('2. Testing API methods (will use debug output)...');

        // Эти методы будут использовать debugConsole внутри
        try {
            $products = $apiService->getProducts();
            $this->info("   Products retrieved: " . (is_array($products) ? count($products) : '0'));

            $stocks = $apiService->getStocks();
            $this->info("   Stocks retrieved: " . (is_array($stocks) ? count($stocks) : '0'));
        } catch (\Exception $e) {
            $this->error("   Error: " . $e->getMessage());
        }

        $this->info('3. Testing debug output directly...');

        // Если нужно протестировать protected методы, используем рефлексию
        $this->testProtectedMethods($apiService);

        $this->info('=== TEST COMPLETED ===');
    }

    protected function testProtectedMethods($apiService)
    {
        try {
            $reflection = new ReflectionClass($apiService);

            // Проверяем существование методов
            if ($reflection->hasMethod('debugConsole')) {
                $this->info("   ✓ debugConsole method exists");
            }

            if ($reflection->hasMethod('debugSeparator')) {
                $this->info("   ✓ debugSeparator method exists");
            }

            if ($reflection->hasMethod('debugTable')) {
                $this->info("   ✓ debugTable method exists");
            }
        } catch (\Exception $e) {
            $this->error("   Reflection error: " . $e->getMessage());
        }
    }
}
