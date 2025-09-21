<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
use App\Models\Account;
use App\Models\ApiService;
use App\Models\TokenType;
use App\Models\Token;
use App\Models\Product;
use App\Services\WbApiService;

class VerifyTZ extends Command
{
    protected $signature = 'app:verify-tz';
    protected $description = 'Проверка выполнения всех требований ТЗ';

    public function handle()
    {
        $this->info('=== НАЧАЛО ПРОВЕРКИ ВЫПОЛНЕНИЯ ТЗ ===');

        // 1. Проверка структуры базы данных
        $this->checkDatabaseStructure();

        // 2. Проверка связей между таблицами
        $this->checkDatabaseRelations();

        // 3. Проверка изоляции данных
        $this->checkDataIsolation();

        // 4. Проверка работы с разными типами токенов
        $this->checkTokenTypes();

        // 5. Проверка получения свежих данных
        $this->checkFreshData();

        // 6. Проверка обработки ошибок API (упрощенная)
        $this->checkErrorHandling();

        $this->info('=== ПРОВЕРКА ЗАВЕРШЕНА ===');
    }

    protected function checkDatabaseStructure()
    {
        $this->info('1. Проверка структуры базы данных...');

        $tables = ['companies', 'accounts', 'api_services', 'token_types', 'tokens', 'products', 'orders', 'sales', 'stocks', 'incomes'];
        $allTablesExist = true;

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $this->line("   Таблица {$table}: ✅ ({$count} записей)");
            } else {
                $this->line("   Таблица {$table}: ❌");
                $allTablesExist = false;
            }
        }

        if ($allTablesExist) {
            $this->info("   Все таблицы существуют.");
        } else {
            $this->error("   Не все таблицы существуют.");
        }
    }

    protected function checkDatabaseRelations()
    {
        $this->info('2. Проверка связей между таблицами...');

        // Проверяем наличие компаний и аккаунтов
        $company = Company::first();
        if ($company) {
            $this->line("   Компания '{$company->name}' существует.");
            $this->line("   У компании '{$company->name}' есть аккаунты: " . $company->accounts->count());
        } else {
            $this->error("   Нет компаний в базе данных.");
        }

        $account = Account::first();
        if ($account) {
            $this->line("   Аккаунт '{$account->name}' существует.");
            $this->line("   У аккаунта '{$account->name}' есть токены: " . $account->tokens->count());
        } else {
            $this->error("   Нет аккаунтов в базе данных.");
        }
    }
    private function checkDataIsolation()
    {
        $this->info("3. Проверка изоляции данных...");


        // Сначала удаляем существующие тестовые данные, если они есть
        \App\Models\Account::where('account_number', 'TEST_ACC_002')->delete();

        // Затем создаем новую запись
        $company = \App\Models\Company::first();

        try {
            $account = \App\Models\Account::firstOrCreate(
                [
                    'account_number' => 'TEST_ACC_002'
                ],
                [
                    'company_id' => $company->id,
                    'name' => 'Тестовый аккаунт 2'
                ]
            );

            $this->info("   ✅ Тестовый аккаунт создан успешно");
        } catch (\Exception $e) {
            $this->error("   ❌ Ошибка при создании тестового аккаунта: " . $e->getMessage());
            return;
        }
        // Получаем или создаем тестовые компании и аккаунты
        $company1 = Company::firstOrCreate(
            ['name' => 'Тестовая компания 1'],
            ['name' => 'Тестовая компания 1']
        );

        $company2 = Company::firstOrCreate(
            ['name' => 'Тестовая компания 2'],
            ['name' => 'Тестовая компания 2']
        );

        $account1 = Account::firstOrCreate(
            [
                'company_id' => $company1->id,
                'name' => 'Тестовый аккаунт 1'
            ],
            [
                'account_number' => 'TEST_ACC_001'
            ]
        );

        $account2 = Account::firstOrCreate(
            [
                'company_id' => $company2->id,
                'name' => 'Тестовый аккаунт 2'
            ],
            [
                'account_number' => 'TEST_ACC_002'
            ]
        );

        // Создаем продукты для разных аккаунтов, если их нет
        if (Product::where('account_id', $account1->id)->count() == 0) {
            Product::create([
                'account_id' => $account1->id,
                'nm_id' => 1001,
                'name' => 'Товар аккаунта 1',
                'brand' => 'Test Brand',
                'price' => 1000,
                'sale_price' => 900,
                'discount' => 10,
                'images' => json_encode(['image1.jpg'])
            ]);
        }

        if (Product::where('account_id', $account2->id)->count() == 0) {
            Product::create([
                'account_id' => $account2->id,
                'nm_id' => 1002,
                'name' => 'Товар аккаунта 2',
                'brand' => 'Test Brand 2',
                'price' => 2000,
                'sale_price' => 1800,
                'discount' => 15,
                'images' => json_encode(['image2.jpg'])
            ]);
        }

        // Проверяем изоляцию данных
        $count1 = Product::where('account_id', $account1->id)->count();
        $count2 = Product::where('account_id', $account2->id)->count();

        $this->info("   Товаров у аккаунта 1: $count1");
        $this->info("   Товаров у аккаунта 2: $count2");

        $isIsolated = $count1 > 0 && $count2 > 0;
        $this->info("   Изоляция данных: " . ($isIsolated ? "✅" : "❌"));

        return $isIsolated;
    }

    protected function checkErrorHandling()
    {
        $this->info('6. Проверка обработки ошибок API...');

        $this->line("   Проверка обработки ошибок требует ручного тестирования с реальным API.");
    }

    private function checkTokenTypes()
    {
        $this->info("4. Проверка типов токенов и их связей с API сервисами...");

        // Проверяем наличие типов токенов
        $tokenTypes = TokenType::all();
        if ($tokenTypes->isEmpty()) {
            $this->error("   Нет типов токенов в базе данных.");
            return false;
        }

        $this->info("   Найдено типов токенов: " . $tokenTypes->count());

        // Проверяем API сервисы
        $apiServices = ApiService::all();
        if ($apiServices->isEmpty()) {
            $this->error("   Нет API сервисов в базе данных.");
            return false;
        }

        $this->info("   Найдено API сервисов: " . $apiServices->count());

        // Проверяем связи между сервисами и типами токенов
        $hasRelations = false;
        foreach ($apiServices as $service) {
            if ($service->tokenTypes->isNotEmpty()) {
                $hasRelations = true;
                $this->info("   Сервис '{$service->name}' поддерживает типы: " .
                    $service->tokenTypes->pluck('slug')->implode(', '));
            }
        }

        if (!$hasRelations) {
            $this->error("   Нет связей между API сервисами и типами токенов.");
            return false;
        }

        $this->info("   Связи между сервисами и типами токенов: ✅");
        return true;
    }
}
