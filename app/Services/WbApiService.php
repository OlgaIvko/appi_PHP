<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WbApiService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'http://109.73.206.144:6969';
        $this->apiKey = 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie';
    }

    public function makeRequest($endpoint, $params = [])
    {
        try {
            $url = $this->baseUrl . '/' . $endpoint;
            $defaultParams = ['key' => $this->apiKey];
            $allParams = array_merge($defaultParams, $params);

            $response = Http::timeout(60)
                ->retry(3, 1000)
                ->get($url, $allParams);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('WB API Error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('WB API Exception', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage()
            ]);
        }

        return null;
    }

    public function getProducts()
    {
        Log::info('Using mock products data for demonstration');
        return $this->getMockProducts();
    }

    public function getStocks()
    {
        Log::info('Using mock stocks data for demonstration');
        return $this->getMockStocks();
    }

    public function getIncomes()
    {
        Log::info('Using mock incomes data for demonstration');
        return $this->getMockIncomes();
    }

    public function getOrders($dateFrom = null)
    {
        Log::info('Using mock orders data for demonstration');
        return $this->getMockOrders();
    }

    public function getSales($dateFrom = null)
    {
        Log::info('Using mock sales data for demonstration');
        return $this->getMockSales();
    }

    private function getMockProducts()
    {
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
            ],
            [
                'nm_id' => 345678,
                'name' => 'Тестовый товар 3',
                'brand' => 'Test Brand 3',
                'price' => 1500,
                'discount' => 20,
                'sale_price' => 1200,
                'images' => ['image4.jpg', 'image5.jpg']
            ]
        ];
    }

    private function getMockStocks()
    {
        return [
            [
                'nmId' => 123456,
                'warehouse' => 'Основной склад',
                'quantity' => 50,
                'inWayToClient' => 10,
                'inWayFromClient' => 5
            ],
            [
                'nmId' => 789012,
                'warehouse' => 'Основной склад',
                'quantity' => 25,
                'inWayToClient' => 3,
                'inWayFromClient' => 2
            ]
        ];
    }

    private function getMockIncomes()
    {
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

    private function getMockOrders()
    {
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

    private function getMockSales()
    {
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
            ],
            [
                'saleID' => 'SALE-002',
                'date' => '2024-01-18 11:20:00',
                'lastChangeDate' => '2024-01-18 11:30:00',
                'warehouseName' => 'Основной склад',
                'countryName' => 'Россия',
                'oblastOkrugName' => 'Центральный федеральный округ',
                'regionName' => 'Московская область',
                'supplierArticle' => 'SUP-002',
                'nmId' => 789012,
                'barcode' => '9876543210987',
                'category' => 'Одежда',
                'subject' => 'Джинсы',
                'brand' => 'Test Brand 2',
                'techSize' => 'L',
                'incomeID' => 1002,
                'isSupply' => false,
                'isRealization' => true,
                'totalPrice' => 2500,
                'discountPercent' => 15,
                'spp' => 200,
                'finishedPrice' => 2125,
                'priceWithDisc' => 2125,
                'isCancel' => false,
                'cancelDate' => null,
                'orderType' => 'Клиентский',
                'sticker' => 'STICKER-002',
                'gNumber' => 'G-002'
            ]
        ];
    }
}
