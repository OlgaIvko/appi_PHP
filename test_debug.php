<?php
require_once 'vendor/autoload.php';

use App\Services\WbApiService;

echo "🎯 Testing WB API Service from XAMPP project\n";
echo "============================================\n";

// Создаем экземпляр сервиса
$apiService = new WbApiService();

// Тестируем debug вывод
echo "\n🧪 Testing debug output:\n";
echo "------------------------\n";

// Вызовем методы чтобы увидеть debug вывод
$products = $apiService->getProducts();
echo "   📦 Products: " . count($products) . " items\n";

$stocks = $apiService->getStocks();
echo "   📦 Stocks: " . count($stocks) . " items\n";

echo "\n🎉 Test completed! Check debug output above.\n";
