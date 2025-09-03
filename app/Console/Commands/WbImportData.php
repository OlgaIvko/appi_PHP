<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WbApiService;
use App\Models\Product;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\Income;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WbImportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wb:import-data
                            {--all : Import all data}
                            {--products : Import products}
                            {--orders : Import orders}
                            {--sales : Import sales}
                            {--stocks : Import stocks}
                            {--incomes : Import incomes}
                            {--date-from= : Date from for orders and sales (format: YYYY-MM-DD)}
                            {--chunk-size=100 : Number of records to process at once}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from Wildberries API';

    protected $apiService;

    /**
     * Create a new command instance.
     */
    public function __construct(WbApiService $apiService)
    {
        parent::__construct();
        $this->apiService = $apiService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Wildberries API data import...');
        $this->line('');

        $startTime = microtime(true);

        try {
            if ($this->option('all')) {
                $this->importAllData();
            } else {
                $this->importSelectedData();
            }

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->line('');
            $this->info("✅ Data import completed successfully! Time: {$executionTime}s");
        } catch (\Exception $e) {
            $this->error('❌ Import failed: ' . $e->getMessage());
            Log::error('WB Import failed', ['error' => $e->getMessage()]);
            return 1;
        }

        return 0;
    }

    private function importAllData()
    {
        $this->info('📦 Importing all data...');

        $dateFrom = $this->option('date-from') ?? date('Y-m-d', strtotime('-7 days'));

        $this->importProducts();
        $this->importStocks();
        $this->importIncomes();
        $this->importOrders($dateFrom);
        $this->importSales($dateFrom);
    }

    private function importSelectedData()
    {
        $dateFrom = $this->option('date-from');
        $importedAny = false;

        if ($this->option('products')) {
            $this->importProducts();
            $importedAny = true;
        }

        if ($this->option('stocks')) {
            $this->importStocks();
            $importedAny = true;
        }

        if ($this->option('incomes')) {
            $this->importIncomes();
            $importedAny = true;
        }

        if ($this->option('orders')) {
            $this->importOrders($dateFrom);
            $importedAny = true;
        }

        if ($this->option('sales')) {
            $this->importSales($dateFrom);
            $importedAny = true;
        }

        if (!$importedAny) {
            $this->warn('⚠️ No import options selected. Use --help to see available options.');
        }
    }

    private function importProducts()
    {
        $this->info('🛍️ Importing products...');
        $products = $this->apiService->getProducts();

        if (!$products || !is_array($products)) {
            $this->error('Failed to fetch products or no products found');
            return;
        }

        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($products as $product) {
            try {
                Product::updateOrCreate(
                    ['nm_id' => $product['nm_id']],
                    [
                        'name' => $product['name'] ?? null,
                        'brand' => $product['brand'] ?? null,
                        'price' => $product['price'] ?? 0,
                        'discount' => $product['discount'] ?? 0,
                        'sale_price' => $product['sale_price'] ?? 0,
                        'images' => isset($product['images']) ? json_encode($product['images']) : null,
                        'updated_at' => now(),
                    ]
                );
                $successCount++;
            } catch (\Exception $e) {
                Log::error('Product import error', [
                    'nm_id' => $product['nm_id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $errorCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("📊 Products: {$successCount} imported, {$errorCount} errors");
    }

    private function importOrders($dateFrom = null)
    {
        $this->info('📋 Importing orders...');
        $orders = $this->apiService->getOrders($dateFrom);

        if (!$orders || !is_array($orders)) {
            $this->error('Failed to fetch orders or no orders found');
            return;
        }

        $bar = $this->output->createProgressBar(count($orders));
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($orders as $order) {
            try {
                Order::updateOrCreate(
                    ['odid' => $order['odid']],
                    [
                        'date' => $order['date'] ?? null,
                        'last_change_date' => $order['lastChangeDate'] ?? null,
                        'warehouse_name' => $order['warehouseName'] ?? null,
                        'country_name' => $order['countryName'] ?? null,
                        'oblast_okrug_name' => $order['oblastOkrugName'] ?? null,
                        'region_name' => $order['regionName'] ?? null,
                        'supplier_article' => $order['supplierArticle'] ?? null,
                        'nm_id' => $order['nmId'] ?? null,
                        'barcode' => $order['barcode'] ?? null,
                        'category' => $order['category'] ?? null,
                        'subject' => $order['subject'] ?? null,
                        'brand' => $order['brand'] ?? null,
                        'tech_size' => $order['techSize'] ?? null,
                        'income_id' => $order['incomeID'] ?? null,
                        'is_supply' => $order['isSupply'] ?? false,
                        'is_realization' => $order['isRealization'] ?? false,
                        'total_price' => $order['totalPrice'] ?? 0,
                        'discount_percent' => $order['discountPercent'] ?? 0,
                        'spp' => $order['spp'] ?? 0,
                        'finished_price' => $order['finishedPrice'] ?? 0,
                        'price_with_disc' => $order['priceWithDisc'] ?? 0,
                        'is_cancel' => $order['isCancel'] ?? false,
                        'cancel_date' => $order['cancelDate'] ?? null,
                        'order_type' => $order['orderType'] ?? null,
                        'sticker' => $order['sticker'] ?? null,
                        'g_number' => $order['gNumber'] ?? null,
                        'updated_at' => now(),
                    ]
                );
                $successCount++;
            } catch (\Exception $e) {
                Log::error('Order import error', [
                    'odid' => $order['odid'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $errorCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("📊 Orders: {$successCount} imported, {$errorCount} errors");
    }

    private function importSales($dateFrom = null)
    {
        $this->info('💰 Importing sales...');
        $sales = $this->apiService->getSales($dateFrom);

        if (!$sales || !is_array($sales)) {
            $this->error('Failed to fetch sales or no sales found');
            return;
        }

        $bar = $this->output->createProgressBar(count($sales));
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($sales as $sale) {
            try {
                Sale::updateOrCreate(
                    ['sale_id' => $sale['saleID']],
                    [
                        'date' => $sale['date'] ?? null,
                        'last_change_date' => $sale['lastChangeDate'] ?? null,
                        'warehouse_name' => $sale['warehouseName'] ?? null,
                        'country_name' => $sale['countryName'] ?? null,
                        'oblast_okrug_name' => $sale['oblastOkrugName'] ?? null,
                        'region_name' => $sale['regionName'] ?? null,
                        'supplier_article' => $sale['supplierArticle'] ?? null,
                        'nm_id' => $sale['nmId'] ?? null,
                        'barcode' => $sale['barcode'] ?? null,
                        'category' => $sale['category'] ?? null,
                        'subject' => $sale['subject'] ?? null,
                        'brand' => $sale['brand'] ?? null,
                        'tech_size' => $sale['techSize'] ?? null,
                        'income_id' => $sale['incomeID'] ?? null,
                        'is_supply' => $sale['isSupply'] ?? false,
                        'is_realization' => $sale['isRealization'] ?? false,
                        'total_price' => $sale['totalPrice'] ?? 0,
                        'discount_percent' => $sale['discountPercent'] ?? 0,
                        'spp' => $sale['spp'] ?? 0,
                        'finished_price' => $sale['finishedPrice'] ?? 0,
                        'price_with_disc' => $sale['priceWithDisc'] ?? 0,
                        'is_cancel' => $sale['isCancel'] ?? false,
                        'cancel_date' => $sale['cancelDate'] ?? null,
                        'order_type' => $sale['orderType'] ?? null,
                        'sticker' => $sale['sticker'] ?? null,
                        'g_number' => $sale['gNumber'] ?? null,
                        'updated_at' => now(),
                    ]
                );
                $successCount++;
            } catch (\Exception $e) {
                Log::error('Sale import error', [
                    'sale_id' => $sale['saleID'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $errorCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("📊 Sales: {$successCount} imported, {$errorCount} errors");
    }

    private function importStocks()
    {
        $this->info('📦 Importing stocks...');
        $stocks = $this->apiService->getStocks();

        if (!$stocks || !is_array($stocks)) {
            $this->error('Failed to fetch stocks or no stocks found');
            return;
        }

        $bar = $this->output->createProgressBar(count($stocks));
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($stocks as $stock) {
            try {
                Stock::updateOrCreate(
                    [
                        'nm_id' => $stock['nmId'],
                        'warehouse' => $stock['warehouse'] ?? 'default'
                    ],
                    [
                        'last_change_date' => $stock['lastChangeDate'] ?? null,
                        'supplier_article' => $stock['supplierArticle'] ?? null,
                        'barcode' => $stock['barcode'] ?? null,
                        'quantity' => $stock['quantity'] ?? 0,
                        'in_way_to_client' => $stock['inWayToClient'] ?? 0,
                        'in_way_from_client' => $stock['inWayFromClient'] ?? 0,
                        'subject' => $stock['subject'] ?? null,
                        'category' => $stock['category'] ?? null,
                        'days_on_site' => $stock['daysOnSite'] ?? 0,
                        'brand' => $stock['brand'] ?? null,
                        'tech_size' => $stock['techSize'] ?? null,
                        'price' => $stock['Price'] ?? 0,
                        'discount' => $stock['Discount'] ?? 0,
                        'updated_at' => now(),
                    ]
                );
                $successCount++;
            } catch (\Exception $e) {
                Log::error('Stock import error', [
                    'nm_id' => $stock['nmId'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $errorCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("📊 Stocks: {$successCount} imported, {$errorCount} errors");
    }

    private function importIncomes()
    {
        $this->info('📥 Importing incomes...');
        $incomes = $this->apiService->getIncomes();

        if (!$incomes || !is_array($incomes)) {
            $this->error('Failed to fetch incomes or no incomes found');
            return;
        }

        $bar = $this->output->createProgressBar(count($incomes));
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($incomes as $income) {
            try {
                Income::updateOrCreate(
                    ['income_id' => $income['incomeId']],
                    [
                        'number' => $income['number'] ?? null,
                        'date' => $income['date'] ?? null,
                        'last_change_date' => $income['lastChangeDate'] ?? null,
                        'supplier_article' => $income['supplierArticle'] ?? null,
                        'tech_size' => $income['techSize'] ?? null,
                        'barcode' => $income['barcode'] ?? null,
                        'quantity' => $income['quantity'] ?? 0,
                        'total_price' => $income['totalPrice'] ?? 0,
                        'date_close' => $income['dateClose'] ?? null,
                        'warehouse_name' => $income['warehouseName'] ?? null,
                        'nm_id' => $income['nmId'] ?? null,
                        'status' => $income['status'] ?? null,
                        'updated_at' => now(),
                    ]
                );
                $successCount++;
            } catch (\Exception $e) {
                Log::error('Income import error', [
                    'income_id' => $income['incomeId'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $errorCount++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("📊 Incomes: {$successCount} imported, {$errorCount} errors");
    }
}
