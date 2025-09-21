<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\Account;

class CompaniesAndAccountsSeeder extends Seeder
{
    public function run()
    {
        // Создаем или обновляем компании
        $company1 = Company::updateOrCreate(
            ['name' => 'Тестовая компания'],
            [
                'description' => 'Это тестовая компания для проверки изоляции данных',
                'is_active' => true,
            ]
        );

        $company2 = Company::updateOrCreate(
            ['name' => 'Вторая тестовая компания'],
            [
                'description' => 'Вторая тестовая компания для проверки изоляции данных',
                'is_active' => true,
            ]
        );

        // Создаем или обновляем аккаунты для первой компании
        Account::updateOrCreate(
            ['account_number' => 'TEST001'],
            [
                'company_id' => $company1->id,
                'name' => 'Тестовый аккаунт 1',
            ]
        );

        Account::updateOrCreate(
            ['account_number' => 'TEST002'],
            [
                'company_id' => $company1->id,
                'name' => 'Тестовый аккаунт 2',
            ]
        );

        // Создаем или обновляем аккаунты для второй компании
        Account::updateOrCreate(
            ['account_number' => 'TEST003'],
            [
                'company_id' => $company2->id,
                'name' => 'Второй аккаунт',
            ]
        );
    }
}
