
    <?php

    use App\Models\Account;
    use App\Models\Product;
    use App\Models\Order;
    use App\Models\Sale;
    use App\Models\Stock;
    use App\Models\Income;
    use Illuminate\Support\Facades\Log;

    class SafeImportService
    {
        public function importDataForAccount($accountId, $data, $type)
        {
            $account = Account::find($accountId);

            if (!$account) {
                throw new \Exception("Account {$accountId} not found");
            }

            switch ($type) {
                case 'products':
                    return $this->importProducts($account, $data);
                case 'orders':
                    return $this->importOrders($account, $data);
                case 'sales':
                    return $this->importSales($account, $data);
                case 'stocks':
                    return $this->importStocks($account, $data);
                case 'incomes':
                    return $this->importIncomes($account, $data);
                default:
                    throw new \Exception("Unknown data type: {$type}");
            }
        }

        public function importDataWithDateCheck($accountId, $data, $type, $dateField = 'date')
        {
            $account = Account::find($accountId);

            if (!$account) {
                throw new \Exception("Account {$accountId} not found");
            }

            $imported = 0;
            $updated = 0;
            $skipped = 0;

            // Получим последнюю дату в базе для этого аккаунта
            $lastDate = $this->getLastDateForAccount($accountId, $type);

            foreach ($data as $itemData) {
                $itemDate = $itemData[$dateField] ?? null;

                // Пропускаем данные старше последней имеющейся даты
                if ($lastDate && $itemDate && $itemDate <= $lastDate) {
                    $skipped++;
                    continue;
                }

                try {
                    // ЗАМЕНИЛИ importItem на прямой вызов соответствующих методов
                    $result = $this->importSingleItem($account, $itemData, $type);
                    $result['action'] == 'created' ? $imported++ : $updated++;
                } catch (\Exception $e) {
                    Log::error("Import error: " . $e->getMessage());
                    $skipped++;
                }
            }

            return [
                'imported' => $imported,
                'updated' => $updated,
                'skipped' => $skipped,
                'last_date' => $lastDate
            ];
        }

        // НОВЫЙ МЕТОД: importSingleItem вместо importItem
        protected function importSingleItem(Account $account, $itemData, $type)
        {
            switch ($type) {
                case 'products':
                    return $this->importProductItem($account, $itemData);
                case 'orders':
                    return $this->importOrderItem($account, $itemData);
                case 'sales':
                    return $this->importSaleItem($account, $itemData);
                case 'stocks':
                    return $this->importStockItem($account, $itemData);
                case 'incomes':
                    return $this->importIncomeItem($account, $itemData);
                default:
                    throw new \Exception("Unknown data type: {$type}");
            }
        }

        // Методы для импорта отдельных элементов
        protected function importProductItem(Account $account, $productData)
        {
            $product = Product::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'nm_id' => $productData['nm_id']
                ],
                [
                    'name' => $productData['name'],
                    'brand' => $productData['brand'],
                    'price' => $productData['price'],
                    'discount' => $productData['discount'] ?? null,
                    'sale_price' => $productData['sale_price'] ?? null,
                    'last_updated' => now()
                ]
            );

            return [
                'action' => $product->wasRecentlyCreated ? 'created' : 'updated',
                'id' => $product->id
            ];
        }

        protected function importOrderItem(Account $account, $orderData)
        {
            $order = Order::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'odid' => $orderData['odid']
                ],
                [
                    'date' => $orderData['date'],
                    'last_change_date' => $orderData['last_change_date'],
                    'warehouse_name' => $orderData['warehouse_name'],
                    'supplier_article' => $orderData['supplier_article'],
                    'nm_id' => $orderData['nm_id'],
                    'barcode' => $orderData['barcode'],
                    'total_price' => $orderData['total_price'],
                    'last_updated' => now()
                ]
            );

            return [
                'action' => $order->wasRecentlyCreated ? 'created' : 'updated',
                'id' => $order->id
            ];
        }

        protected function importSaleItem(Account $account, $saleData)
        {
            $sale = Sale::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'sale_id' => $saleData['saleID']
                ],
                [
                    'date' => $saleData['date'],
                    'last_change_date' => $saleData['lastChangeDate'],
                    'warehouse_name' => $saleData['warehouseName'],
                    'supplier_article' => $saleData['supplierArticle'],
                    'nm_id' => $saleData['nmId'],
                    'barcode' => $saleData['barcode'],
                    'total_price' => $saleData['totalPrice'],
                    'last_updated' => now()
                ]
            );

            return [
                'action' => $sale->wasRecentlyCreated ? 'created' : 'updated',
                'id' => $sale->id
            ];
        }

        protected function importStockItem(Account $account, $stockData)
        {
            $stock = Stock::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'nm_id' => $stockData['nmId']
                ],
                [
                    'warehouse' => $stockData['warehouseName'],
                    'quantity' => $stockData['quantity'],
                    'in_way_to_client' => $stockData['inWayToClient'] ?? 0,
                    'in_way_from_client' => $stockData['inWayFromClient'] ?? 0,
                    'last_updated' => now()
                ]
            );

            return [
                'action' => $stock->wasRecentlyCreated ? 'created' : 'updated',
                'id' => $stock->id
            ];
        }

        protected function importIncomeItem(Account $account, $incomeData)
        {
            $income = Income::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'income_id' => $incomeData['incomeId']
                ],
                [
                    'number' => $incomeData['number'],
                    'date' => $incomeData['date'],
                    'last_change_date' => $incomeData['lastChangeDate'],
                    'supplier_article' => $incomeData['supplierArticle'],
                    'tech_size' => $incomeData['techSize'],
                    'barcode' => $incomeData['barcode'],
                    'quantity' => $incomeData['quantity'],
                    'total_price' => $incomeData['totalPrice'],
                    'date_close' => $incomeData['dateClose'],
                    'warehouse_name' => $incomeData['warehouseName'],
                    'nm_id' => $incomeData['nmId'],
                    'status' => $incomeData['status'],
                    'last_updated' => now()
                ]
            );

            return [
                'action' => $income->wasRecentlyCreated ? 'created' : 'updated',
                'id' => $income->id
            ];
        }

        protected function getLastDateForAccount($accountId, $type)
        {
            switch ($type) {
                case 'orders':
                    return Order::where('account_id', $accountId)->max('date');
                case 'sales':
                    return Sale::where('account_id', $accountId)->max('date');
                case 'incomes':
                    return Income::where('account_id', $accountId)->max('date');
                default:
                    return null;
            }
        }

        // Оригинальные методы массового импорта
        protected function importProducts(Account $account, $productsData)
        {
            $imported = 0;
            $updated = 0;

            foreach ($productsData as $productData) {
                $result = $this->importProductItem($account, $productData);
                $result['action'] == 'created' ? $imported++ : $updated++;
            }

            return ['imported' => $imported, 'updated' => $updated];
        }

        protected function importOrders(Account $account, $ordersData)
        {
            $imported = 0;
            $updated = 0;

            foreach ($ordersData as $orderData) {
                $result = $this->importOrderItem($account, $orderData);
                $result['action'] == 'created' ? $imported++ : $updated++;
            }

            return ['imported' => $imported, 'updated' => $updated];
        }

        protected function importSales(Account $account, $salesData)
        {
            $imported = 0;
            $updated = 0;

            foreach ($salesData as $saleData) {
                $result = $this->importSaleItem($account, $saleData);
                $result['action'] == 'created' ? $imported++ : $updated++;
            }

            return ['imported' => $imported, 'updated' => $updated];
        }

        protected function importStocks(Account $account, $stocksData)
        {
            $imported = 0;
            $updated = 0;

            foreach ($stocksData as $stockData) {
                $result = $this->importStockItem($account, $stockData);
                $result['action'] == 'created' ? $imported++ : $updated++;
            }

            return ['imported' => $imported, 'updated' => $updated];
        }

        protected function importIncomes(Account $account, $incomesData)
        {
            $imported = 0;
            $updated = 0;

            foreach ($incomesData as $incomeData) {
                $result = $this->importIncomeItem($account, $incomeData);
                $result['action'] == 'created' ? $imported++ : $updated++;
            }

            return ['imported' => $imported, 'updated' => $updated];
        }


        // protected function getLastDateForAccount($accountId, $type)
        // {
        //     switch ($type) {
        //         case 'orders':
        //             return Order::where('account_id', $accountId)->max('date');
        //         case 'sales':
        //             return Sale::where('account_id', $accountId)->max('date');
        //         case 'incomes':
        //             return Income::where('account_id', $accountId)->max('date');
        //         default:
        //             return null;
        //     }
        // }

    }
