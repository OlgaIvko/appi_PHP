<?php
require_once 'vendor/autoload.php';

use App\Services\WbApiService;

echo "🔍 Diagnostic Test\n";
echo "=================\n";

// Создаем экземпляр
$apiService = new WbApiService();

// Проверяем значение debug
$reflection = new ReflectionClass($apiService);
$debugProperty = $reflection->getProperty('debug');
$debugProperty->setAccessible(true);
$debugValue = $debugProperty->getValue($apiService);

echo "1. Debug property value: " . ($debugValue ? 'TRUE' : 'FALSE') . "\n";

// Проверяем что метод существует
$methods = get_class_methods($apiService);
echo "2. Methods available: " . implode(', ', $methods) . "\n";

// Пробуем вызвать метод напрямую через call_user_func
echo "3. Testing direct call:\n";
if (method_exists($apiService, 'debugConsole')) {
    // Делаем метод доступным
    $method = $reflection->getMethod('debugConsole');
    $method->setAccessible(true);
    
    echo "   - Method exists, calling...\n";
    $method->invoke($apiService, 'DIRECT CALL: Test message', 'info');
} else {
    echo "   - Method does not exist!\n";
}

// Тестируем вызов публичных методов
echo "4. Testing public methods:\n";
$apiService->getProducts();
echo "   - getProducts() called\n";

$apiService->getStocks();
echo "   - getStocks() called\n";

echo "=================\n";
echo "Diagnostic completed.\n";
