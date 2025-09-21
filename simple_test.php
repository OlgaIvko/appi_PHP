<?php
require_once 'vendor/autoload.php';

use App\Services\WbApiService;

// Простой тест без лишнего вывода
$apiService = new WbApiService();

// Просто вызываем методы - debug вывод должен появиться автоматически
$products = $apiService->getProducts();
$stocks = $apiService->getStocks();

echo "Basic test completed.\n";
