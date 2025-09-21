<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TokenType;

class MakeTokenType extends Command
{
    protected $signature = 'make:token-type {name} {slug} {description?}';
    protected $description = 'Create a new token type';

    public function handle()
    {
        $this->info("=== НАЧАЛО ПРОВЕРКИ ВЫПОЛНЕНИЯ ТЗ ===");

        $results = [
            'database_structure' => $this->checkDatabaseStructure(),
            'data_isolation' => $this->checkDataIsolation(),
            'token_types' => $this->checkTokenTypes(),
            // Добавьте другие проверки по необходимости
        ];

        // Вывод итогов
        $this->info("\n=== ИТОГИ ПРОВЕРКИ ===");
        foreach ($results as $check => $result) {
            $status = $result ? '✅' : '❌';
            $this->info("$check: $status");
        }

        // Возвращаем код ошибки, если есть неудачные проверки
        return in_array(false, $results) ? 1 : 0;
    }
}
