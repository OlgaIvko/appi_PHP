<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Account;

class CompaniesAndAccountsSeeder extends Seeder
{
    public function run()
    {
        // Создаем компании
        $company1 = Company::firstOrCreate(
            ['name' => 'Тестовая компания'],
            [
                'description' => 'Это тестовая компания для проверки изоляции данных',
                'is_active' => true,
            ]
        );

        $company2 = Company::firstOrCreate(
            ['name' => 'Вторая тестовая компания'],
            [
                'description' => 'Вторая тестовая компания для проверки изоляции данных',
                'is_active' => true,
            ]
        );

        // Создаем аккаунты для первой компании
        Account::firstOrCreate(
            ['account_number' => 'TEST001'],
            [
                'company_id' => $company1->id,
                'name' => 'Тестовый аккаунт 1',
            ]
        );

        Account::firstOrCreate(
            ['account_number' => 'TEST002'],
            [
                'company_id' => $company1->id,
                'name' => 'Тестовый аккаунт 2',
            ]
        );

        // Создаем аккаунты для второй компании
        Account::firstOrCreate(
            ['account_number' => 'TEST003'],
            [
                'company_id' => $company2->id,
                'name' => 'Второй аккаунт',
            ]
        );
    }
}
