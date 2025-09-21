<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            ApiServicesSeeder::class,
            CompaniesAndAccountsSeeder::class,
            TokenTypesSeeder::class,
            TokensSeeder::class,
        ]);
    }
}
