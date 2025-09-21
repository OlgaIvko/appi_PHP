<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Token;
use App\Models\ApiService;

class WbApiService
{
    protected $baseUrl;
    protected $token;
    protected $tokenType;
    protected $apiKey;
    protected $maxRetries = 3;
    protected $retryDelay = 1000;
    protected $rateLimitDelay = 5000;
    protected $debug = true;

    // public function __construct()
    // {
    //     $this->baseUrl = 'http://109.73.206.144:6969';
    //     $this->apiKey = 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie';
    //     $this->debug = env('APP_DEBUG', false);

    //     $this->debugConsole("🔄 WbApiService initialized", 'info');
    //     $this->debugConsole("🌐 Base URL: {$this->baseUrl}", 'debug');
    // }
    public function __construct($accountId = null, $apiServiceId = null)
    {
        $this->baseUrl = 'http://109.73.206.144:6969';
        $this->apiKey = 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie';
        $this->debug = env('APP_DEBUG', false);

        // Только если переданы оба параметра, пытаемся найти токен в БД
        if ($accountId && $apiServiceId) {
            $token = Token::where('account_id', $accountId)
                ->where('api_service_id', $apiServiceId)
                ->where('is_active', true)
                ->first();

            if (!$token) {
                throw new \Exception("Token not found for account {$accountId} and service {$apiServiceId}");
            }

            $apiService = ApiService::find($apiServiceId);
            if (!$apiService) {
                throw new \Exception("API Service not found with ID: {$apiServiceId}");
            }

            $this->apiKey = $token->access_token;
            $this->baseUrl = $apiService->base_url;
            $this->tokenType = $token->tokenType->slug;
        }

        $this->debugConsole("🔄 WbApiService initialized", 'info');
        $this->debugConsole("🌐 Base URL: {$this->baseUrl}", 'debug');

        if (isset($this->tokenType)) {
            $this->debugConsole("🔑 Token Type: {$this->tokenType}", 'debug');
        }
    }


    public function setTokenFromDatabase($accountId, $apiServiceId)
    {
        $token = Token::getActiveToken($accountId, $apiServiceId);

        if (!$token) {
            throw new \Exception("No active token found for account {$accountId} and service {$apiServiceId}");
        }

        $this->apiKey = $token->access_token;
        $this->tokenType = $token->tokenType->slug;

        $this->debugConsole("🔑 Using token from database. Type: {$this->tokenType}", 'info');
        return $this;
    }

    protected function prepareHeaders()
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        switch ($this->tokenType) {
            case 'bearer':
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
                break;
            case 'api-key':
                $headers['X-API-Key'] = $this->apiKey;
                break;
            case 'basic':
                $headers['Authorization'] = 'Basic ' . base64_encode($this->apiKey);
                break;
            default:
                $headers['Authorization'] = $this->apiKey;
        }

        return $headers;
    }
    /**
     * Вывод отладочной информации в консоль
     */
    public function debugConsole($message, $level = 'info')
    {
        if (!$this->debug) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s.v');
        $colors = [
            'info' => '1;34',    // Синий
            'success' => '1;32', // Зеленый
            'warning' => '1;33', // Желтый
            'error' => '1;31',   // Красный
            'debug' => '1;36',   // Голубой
            'request' => '1;35', // Фиолетовый (для запросов)
            'response' => '1;32', // Зеленый (для ответов)
        ];

        $colorCode = $colors[$level] ?? '1;37';

        // Добавляем префиксы для разных уровней
        $prefixes = [
            'info' => 'ℹ️ ',
            'success' => '✅ ',
            'warning' => '⚠️ ',
            'error' => '❌ ',
            'debug' => '🐛 ',
            'request' => '🚀 ',
            'response' => '📨 ',
        ];

        $prefix = $prefixes[$level] ?? '';
        $formattedMessage = "\033[{$colorCode}m[{$timestamp}] {$prefix}{$message}\033[0m";

        // Вывод в консоль
        fwrite(STDERR, $formattedMessage . PHP_EOL);

        // Логирование в файл для важных событий
        if (in_array($level, ['error', 'warning'])) {
            Log::{$level}($message);
        }
    }

    /**
     * Вывод разделителя для лучшей читаемости
     */
    public function debugSeparator($title = '', $level = 'info')
    {
        $separator = str_repeat('=', 60);
        if ($title) {
            $this->debugConsole($separator, $level);
            $this->debugConsole(str_pad($title, 60, ' ', STR_PAD_BOTH), $level);
            $this->debugConsole($separator, $level);
        } else {
            $this->debugConsole($separator, $level);
        }
    }

    public function discoverEndpoints()
    {
        $endpoints = ['', '/api', '/api/v1', '/api/v2', '/api/wb', '/v1', '/v2'];
        $resources = ['/products', '/incomes', '/stocks', '/orders', '/sales', '/cache'];

        foreach ($endpoints as $endpoint) {
            foreach ($resources as $resource) {
                $url = 'http://109.73.206.144:6969' . $endpoint . $resource;
                $response = Http::timeout(5)->get($url, ['key' => $this->apiKey]);

                $this->debugConsole("Testing: {$url}", 'info');
                $this->debugConsole(
                    "Status: " . $response->status(),
                    $response->successful() ? 'success' : 'debug'
                );

                if ($response->successful()) {
                    $this->debugConsole("✅ Found working endpoint: " . $endpoint . $resource, 'success');
                    return $endpoint . $resource;
                }
            }
        }

        return null;
    }

    /**
     * Вывод данных в формате таблицы
     */
    public function debugTable($data, $title = '')
    {
        if (!$this->debug || empty($data)) {
            return;
        }

        if ($title) {
            $this->debugSeparator($title, 'debug');
        }

        if (is_array($data) && !empty($data)) {
            $firstItem = reset($data);
            if (is_array($firstItem)) {
                // Вывод массива массивов
                $headers = array_keys($firstItem);
                $this->debugConsole("📊 " . implode(' | ', $headers), 'debug');

                foreach ($data as $index => $item) {
                    $row = array_map(function ($value) {
                        if (is_array($value)) return json_encode($value);
                        if (is_bool($value)) return $value ? 'true' : 'false';
                        return substr((string)$value, 0, 30);
                    }, array_values($item));

                    $this->debugConsole(($index + 1) . ". " . implode(' | ', $row), 'debug');
                }
            } else {
                // Вывод простого массива
                foreach ($data as $key => $value) {
                    $this->debugConsole("   {$key}: " . (is_array($value) ? json_encode($value) : $value), 'debug');
                }
            }
        }

        if ($title) {
            $this->debugSeparator('', 'debug');
        }
    }

    /**
     * Вывод информации о запросе
     */
    public function debugRequestInfo($method, $url, $params)
    {
        $this->debugConsole("📋 Method: {$method}", 'request');
        $this->debugConsole("🌐 URL: {$url}", 'request');
        $this->debugConsole("📊 Parameters: " . json_encode($params, JSON_UNESCAPED_UNICODE), 'request');
    }

    /**
     * Вывод информации о ответе
     */
    public function debugResponseInfo($response, $responseTime)
    {
        $this->debugConsole("⏱️ Response time: {$responseTime}ms", 'response');
        $this->debugConsole("📡 HTTP Status: " . $response->status(), 'response');
        $this->debugConsole("📦 Content-Type: " . $response->header('Content-Type', 'N/A'), 'response');
    }

    /**
     * Включение/выключение debug режима
     */
    public function setDebug($enabled)
    {
        $this->debug = (bool) $enabled;
        $this->debugConsole("🔧 Debug mode: " . ($enabled ? 'ON' : 'OFF'), 'info');
        return $this;
    }

    public function makeRequest($endpoint, $params = [], $method = 'GET')
    {

        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $headers = $this->prepareHeaders();

        $this->debugConsole(" Full URL: {$url}", 'debug');

        $defaultParams = ['key' => $this->apiKey];
        $allParams = array_merge($defaultParams, $params);

        // $response = Http::timeout(30)
        //     ->withHeaders([
        //         'Authorization' => 'Bearer ' . $this->apiKey,
        //         'Content-Type' => 'application/json',
        //         'Accept' => 'application/json'
        //     ])
        $response = Http::timeout(30)
            ->withHeaders($headers)
            ->{$method}($endpoint, $params);

        $url = $this->baseUrl . '/' . $endpoint;
        $defaultParams = ['key' => $this->apiKey];
        $allParams = array_merge($defaultParams, $params);

        $retryCount = 0;

        $this->debugConsole("🚀 Starting request to: {$endpoint}", 'info');
        $this->debugConsole("📋 URL: {$url}", 'debug');
        $this->debugConsole("📊 Params: " . json_encode($allParams), 'debug');

        while ($retryCount <= $this->maxRetries) {
            try {
                $this->debugConsole("🔄 Attempt " . ($retryCount + 1) . "/" . ($this->maxRetries + 1), 'info');

                if ($this->isRateLimited()) {
                    $waitTime = $this->getRateLimitWaitTime();
                    $this->debugConsole("⏳ Rate limit active, waiting: {$waitTime}ms", 'warning');
                    usleep($waitTime * 1000);
                }

                $startTime = microtime(true);

                $response = Http::timeout(30)
                    ->retry(0)
                    ->{$method}($url, $allParams);

                $responseTime = round((microtime(true) - $startTime) * 1000, 2);

                $this->debugConsole("⏱️ Response time: {$responseTime}ms", 'debug');
                $this->debugConsole("📡 HTTP Status: " . $response->status(), 'debug');

                if ($response->successful()) {
                    $this->debugConsole("✅ Request successful", 'success');
                    $this->updateRateLimitStats($response);

                    $data = $response->json();
                    $this->debugConsole("📦 Response items: " . (is_array($data) ? count($data) : '1'), 'debug');

                    return $data;
                }

                if ($response->status() === 429) {
                    $retryAfter = $this->parseRetryAfter($response) ?? $this->rateLimitDelay;

                    $this->debugConsole("🚫 Rate limit exceeded (429)", 'error');
                    $this->debugConsole("⏰ Retry after: {$retryAfter}ms", 'warning');

                    if ($retryCount < $this->maxRetries) {
                        $this->setRateLimit($retryAfter);
                        usleep($retryAfter * 1000);
                        $retryCount++;
                        continue;
                    }
                }

                $this->debugConsole("❌ Request failed with status: " . $response->status(), 'error');
                $this->debugConsole("📄 Response body: " . substr($response->body(), 0, 200) . "...", 'debug');
            } catch (\Exception $e) {
                $this->debugConsole("💥 Exception: " . $e->getMessage(), 'error');
                $this->debugConsole("📋 File: " . $e->getFile() . ":" . $e->getLine(), 'debug');

                if ($retryCount < $this->maxRetries) {
                    $retryCount++;
                    $sleepTime = $this->retryDelay * 1000 * $retryCount;
                    $this->debugConsole("😴 Retrying in: {$sleepTime}ms", 'warning');
                    usleep($sleepTime);
                    continue;
                }
            }

            break;
        }

        $this->debugConsole("🔴 All attempts failed for endpoint: {$endpoint}", 'error');
        return null;
    }

    public function getProducts()
    {
        $this->debugConsole("🛍️  Fetching products...", 'info');

        try {
            $data = $this->makeRequestWithBackoff('products');
            if ($data) {
                $this->debugConsole("✅ Successfully received " . count($data) . " products", 'success');
                return $data;
            }

            $this->debugConsole("📦 Falling back to mock products data", 'warning');
            $mockData = $this->getMockProducts();
            $this->debugConsole("📊 Mock products loaded: " . count($mockData) . " items", 'debug');
            return $mockData;
        } catch (\Exception $e) {
            $this->debugConsole("💥 Failed to get products: " . $e->getMessage(), 'error');
            return $this->getMockProducts();
        }
    }

    public function getStocks()
    {
        $this->debugConsole("📦 Fetching stocks...", 'info');

        try {
            $data = $this->makeRequestWithBackoff('stocks');
            if ($data) {
                $this->debugConsole("✅ Successfully received " . count($data) . " stock items", 'success');
                return $data;
            }

            $this->debugConsole("📦 Falling back to mock stocks data", 'warning');
            return $this->getMockStocks();
        } catch (\Exception $e) {
            $this->debugConsole("💥 Failed to get stocks: " . $e->getMessage(), 'error');
            return $this->getMockStocks();
        }
    }

    public function getIncomes()
    {
        $this->debugConsole("📥 Fetching incomes...", 'info');

        try {
            $data = $this->makeRequestWithBackoff('incomes');
            if ($data) {
                $this->debugConsole("✅ Successfully received " . count($data) . " income items", 'success');
                return $data;
            }

            $this->debugConsole("📦 Falling back to mock incomes data", 'warning');
            return $this->getMockIncomes();
        } catch (\Exception $e) {
            $this->debugConsole("💥 Failed to get incomes: " . $e->getMessage(), 'error');
            return $this->getMockIncomes();
        }
    }

    public function getOrders($dateFrom = null)
    {
        $this->debugConsole("📋 Fetching orders...", 'info');

        try {
            $params = [];
            if ($dateFrom) {
                $params['dateFrom'] = $dateFrom;
                $this->debugConsole("📅 Using date filter: {$dateFrom}", 'debug');
            }

            $data = $this->makeRequestWithBackoff('orders', $params);
            if ($data) {
                $this->debugConsole("✅ Successfully received " . count($data) . " orders", 'success');
                return $data;
            }

            $this->debugConsole("📦 Falling back to mock orders data", 'warning');
            return $this->getMockOrders();
        } catch (\Exception $e) {
            $this->debugConsole("💥 Failed to get orders: " . $e->getMessage(), 'error');
            return $this->getMockOrders();
        }
    }

    public function getSales($dateFrom = null)
    {
        $this->debugConsole("💰 Fetching sales...", 'info');

        try {
            $params = [];
            if ($dateFrom) {
                $params['dateFrom'] = $dateFrom;
                $this->debugConsole("📅 Using date filter: {$dateFrom}", 'debug');
            }

            $data = $this->makeRequestWithBackoff('sales', $params);
            if ($data) {
                $this->debugConsole("✅ Successfully received " . count($data) . " sales", 'success');
                return $data;
            }

            $this->debugConsole("📦 Falling back to mock sales data", 'warning');
            return $this->getMockSales();
        } catch (\Exception $e) {
            $this->debugConsole("💥 Failed to get sales: " . $e->getMessage(), 'error');
            return $this->getMockSales();
        }
    }

    public function makeRequestWithBackoff($endpoint, $params = [], $maxRetries = 5, $initialDelay = 1000)
    {
        $retryCount = 0;
        $delay = $initialDelay;

        $this->debugConsole("⚡ Using exponential backoff for: {$endpoint}", 'debug');

        while ($retryCount <= $maxRetries) {
            $result = $this->makeRequest($endpoint, $params);

            if ($result !== null) {
                return $result;
            }

            if ($retryCount < $maxRetries) {
                $this->debugConsole("🔄 Retry {$retryCount}/{$maxRetries}, delay: {$delay}ms", 'warning');
                usleep($delay * 1000);
                $delay *= 2;
                $retryCount++;
            }
        }

        $this->debugConsole("🔴 Exponential backoff failed for: {$endpoint}", 'error');
        return null;
    }

    // Остальные методы (getMockProducts, getMockStocks и т.д.) остаются без изменений
    public function getMockProducts()
    {
        $this->debugConsole("🔄 Loading mock products data", 'debug');
        return [
            [
                'nm_id' => 123456,
                'name' => 'Тестовый товар 1',
                'brand' => 'Test Brand',
                'price' => 1000,
                'discount' => 10,
                'sale_price' => 900,
                'images' => ['image1.jpg', 'image2.jpg']
            ],
            [
                'nm_id' => 789012,
                'name' => 'Тестовый товар 2',
                'brand' => 'Test Brand 2',
                'price' => 2000,
                'discount' => 15,
                'sale_price' => 1700,
                'images' => ['image3.jpg']
            ]
        ];
    }

    public function getMockStocks()
    {
        $this->debugConsole("🔄 Loading mock stocks data", 'debug');
        return [
            [
                'nmId' => 123456,
                'warehouse' => 'Основной склад',
                'quantity' => 50,
                'inWayToClient' => 10,
                'inWayFromClient' => 5
            ]
        ];
    }

    public function getMockIncomes()
    {
        $this->debugConsole("🔄 Loading mock incomes data", 'debug');
        return [
            [
                'incomeId' => 1001,
                'number' => 'INV-001',
                'date' => '2024-01-15',
                'lastChangeDate' => '2024-01-15 10:30:00',
                'supplierArticle' => 'SUP-001',
                'techSize' => 'M',
                'barcode' => '1234567890123',
                'quantity' => 100,
                'totalPrice' => 50000,
                'dateClose' => '2024-01-20',
                'warehouseName' => 'Основной склад',
                'nmId' => 123456,
                'status' => 'Завершено'
            ]
        ];
    }

    public function getMockOrders()
    {
        $this->debugConsole("🔄 Loading mock orders data", 'debug');
        return [
            [
                'odid' => 'ORDER-001',
                'date' => '2024-01-16 14:30:00',
                'lastChangeDate' => '2024-01-16 15:00:00',
                'warehouseName' => 'Основной склад',
                'countryName' => 'Россия',
                'oblastOkrugName' => 'Центральный федеральный округ',
                'regionName' => 'Московская область',
                'supplierArticle' => 'SUP-001',
                'nmId' => 123456,
                'barcode' => '1234567890123',
                'category' => 'Одежда',
                'subject' => 'Футболки',
                'brand' => 'Test Brand',
                'techSize' => 'M',
                'incomeID' => 1001,
                'isSupply' => false,
                'isRealization' => true,
                'totalPrice' => 1200,
                'discountPercent' => 10,
                'spp' => 100,
                'finishedPrice' => 1080,
                'priceWithDisc' => 1080,
                'isCancel' => false,
                'cancelDate' => null,
                'orderType' => 'Клиентский',
                'sticker' => 'STICKER-001',
                'gNumber' => 'G-001'
            ]
        ];
    }

    public function getMockSales()
    {
        $this->debugConsole("🔄 Loading mock sales data", 'debug');
        return [
            [
                'saleID' => 'SALE-001',
                'date' => '2024-01-17 16:45:00',
                'lastChangeDate' => '2024-01-17 17:00:00',
                'warehouseName' => 'Основной склад',
                'countryName' => 'Россия',
                'oblastOkrugName' => 'Центральный федеральный округ',
                'regionName' => 'Московская область',
                'supplierArticle' => 'SUP-001',
                'nmId' => 123456,
                'barcode' => '1234567890123',
                'category' => 'Одежда',
                'subject' => 'Футболки',
                'brand' => 'Test Brand',
                'techSize' => 'M',
                'incomeID' => 1001,
                'isSupply' => false,
                'isRealization' => true,
                'totalPrice' => 1200,
                'discountPercent' => 10,
                'spp' => 100,
                'finishedPrice' => 1080,
                'priceWithDisc' => 1080,
                'isCancel' => false,
                'cancelDate' => null,
                'orderType' => 'Клиентский',
                'sticker' => 'STICKER-001',
                'gNumber' => 'G-001'
            ]
        ];
    }

    public function parseRetryAfter($response)
    {
        $retryAfter = $response->header('Retry-After');

        if (is_numeric($retryAfter)) {
            return (int) $retryAfter * 1000;
        }

        if ($retryAfter) {
            $retryTime = strtotime($retryAfter);
            if ($retryTime !== false) {
                return max(0, ($retryTime - time()) * 1000);
            }
        }

        return null;
    }

    public function isRateLimited()
    {
        return Cache::get('wb_api_rate_limit', false);
    }

    protected function setRateLimit($delayMs)
    {
        Cache::put('wb_api_rate_limit', true, now()->addMilliseconds($delayMs));
        Cache::put('wb_api_rate_limit_expires', now()->addMilliseconds($delayMs), now()->addHours(1));
    }

    protected function getRateLimitWaitTime()
    {
        $expires = Cache::get('wb_api_rate_limit_expires');
        if ($expires) {
            return max(0, now()->diffInMilliseconds($expires));
        }
        return 0;
    }

    public function updateRateLimitStats($response)
    {
        $limits = [
            'x-ratelimit-limit' => $response->header('x-ratelimit-limit'),
            'x-ratelimit-remaining' => $response->header('x-ratelimit-remaining'),
            'x-ratelimit-reset' => $response->header('x-ratelimit-reset'),
        ];

        if ($limits['x-ratelimit-remaining'] === '0') {
            $resetTime = $limits['x-ratelimit-reset'] ?? 60;
            $this->setRateLimit($resetTime * 1000);
        }

        Cache::put('wb_api_rate_limit_stats', $limits, now()->addMinutes(5));
    }


    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getFreshProducts($accountId, $hours = 24)
    {
        $lastUpdate = Product::where('account_id', $accountId)
            ->max('last_updated');

        $params = [];
        if ($lastUpdate) {
            $params['dateFrom'] = $lastUpdate->format('Y-m-d\TH:i:s');
        }

        $this->debugConsole("🕒 Getting products updated after: " . ($lastUpdate ? $lastUpdate->format('Y-m-d H:i') : 'never'), 'info');

        return $this->makeRequest('products', $params);
    }

    // public function getFreshOrders($accountId, $days = 7)
    // {
    //     $lastDate = Order::where('account_id', $accountId)
    //         ->max('date');

    //     $params = [];
    //     if ($lastDate) {
    //         $params['dateFrom'] = $lastDate;
    //     } else {
    //         $params['dateFrom'] = now()->subDays($days)->format('Y-m-d');
    //     }

    //     $this->debugConsole("📅 Getting orders from: {$params['dateFrom']}", 'info');

    //     return $this->makeRequest('orders', $params);
    // }

    public function getFreshOrders($accountId, $days = 7)
    {
        $lastDate = Order::where('account_id', $accountId)
            ->max('date');

        $params = [];
        if ($lastDate) {
            $params['dateFrom'] = $lastDate;
        } else {
            $params['dateFrom'] = now()->subDays($days)->format('Y-m-d');
        }

        $this->debugConsole("📅 Getting orders from: {$params['dateFrom']}", 'info');

        return $this->makeRequest('orders', $params);
    }

    public function getFreshSales($accountId, $days = 7)
    {
        $lastDate = Sale::where('account_id', $accountId)
            ->max('date');

        $params = [];
        if ($lastDate) {
            $params['dateFrom'] = $lastDate;
        } else {
            $params['dateFrom'] = now()->subDays($days)->format('Y-m-d');
        }

        $this->debugConsole("💰 Getting sales from: {$params['dateFrom']}", 'info');

        return $this->makeRequest('sales', $params);
    }
}
