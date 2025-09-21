<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Account;

class UpdateDataCommand extends Command
{
    protected $signature = 'update:data';
    protected $description = 'Update data from external API';

    public function handle()
    {
        $this->info('Starting data update process...');

        // Получаем все активные аккаунты
        $accounts = Account::with('company', 'tokens')->get();

        foreach ($accounts as $account) {
            $this->processAccount($account);
        }

        $this->info('Data update process completed.');
    }

    protected function processAccount($account)
    {
        $this->info("Processing account: {$account->name}");

        try {
            // Ваша логика получения данных с обработкой ошибок
            $response = Http::timeout(30)
                ->retry(3, 1000, function ($exception) {
                    return $exception->getCode() === 429; // Retry on rate limit
                })
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $account->token,
                ])
                ->get('https://api.example.com/data');

            if ($response->successful()) {
                // Обработка успешного ответа
                $this->info("Data fetched successfully for account: {$account->name}");
            } else if ($response->status() === 429) {
                $this->warn("Rate limit exceeded for account: {$account->name}");
                sleep(60); // Wait 60 seconds before retrying
                $this->processAccount($account); // Retry
            } else {
                $this->error("Error fetching data for account: {$account->name}");
            }
        } catch (\Exception $e) {
            $this->error("Exception for account {$account->name}: " . $e->getMessage());
            Log::error("API Error for account {$account->name}: " . $e->getMessage());
        }
    }
}
