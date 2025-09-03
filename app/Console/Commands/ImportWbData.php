<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WbApiService;
use App\Models\Product;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\Income;

class ImportWbData extends Command
{
    protected $signature = 'wb:import
                            {--all : Import all data}
                            {--products : Import products}
                            {--orders : Import orders}
                            {--sales : Import sales}
                            {--stocks : Import stocks}
                            {--incomes : Import incomes}
                            {--date-from= : Date from for orders and sales}';

    protected $description = 'Import data from Wildberries API';

    public function handle()
    {
        $apiService = new WbApiService();

        if ($this->option('all')) {
            $this->importAllData($apiService);
        } else {
            $this->importSelectedData($apiService);
        }

        $this->info('Data import completed!');
    }

    private function importAllData($apiService)
    {
        $this->importProducts($apiService);
        $this->importStocks($apiService);
        $this->importIncomes($apiService);
        $this->importOrders($apiService, $this->option('date-from'));
        $this->importSales($apiService, $this->option('date-from'));
    }

    private function importSelectedData($apiService)
    {
        if ($this->option('products')) $this->importProducts($apiService);
        if ($this->option('stocks')) $this->importStocks($apiService);
        if ($this->option('incomes')) $this->importIncomes($apiService);
        if ($this->option('orders')) $this->importOrders($apiService, $this->option('date-from'));
        if ($this->option('sales')) $this->importSales($apiService, $this->option('date-from'));
    }

    private function importProducts($apiService)
    {
        $this->info('Importing products...');
        $products = $apiService->getProducts();

        if ($products) {
            foreach ($products as $product) {
                Product::updateOrCreate(
                    ['nm_id' => $product['nm_id']],
                    $product
                );
            }
            $this->info("Imported " . count($products) . " products");
        }
    }

    // Аналогичные методы для других сущностей...
}
