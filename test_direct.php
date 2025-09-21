<?php
require_once 'vendor/autoload.php';

use App\Services\WbApiService;

$apiService = new WbApiService();

// Тестируем напрямую через reflection
$reflection = new ReflectionClass($apiService);
$method = $reflection->getMethod('debugConsole');
$method->setAccessible(true);

echo "Testing debugConsole directly:\n";
$method->invoke($apiService, 'TEST: This is a test message', 'info');
$method->invoke($apiService, 'TEST: Success message', 'success');
$method->invoke($apiService, 'TEST: Warning message', 'warning');

echo "Direct test completed.\n";
