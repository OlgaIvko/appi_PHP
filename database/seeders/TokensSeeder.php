<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Token;
use App\Models\Account;
use App\Models\ApiService;
use App\Models\TokenType;

class TokensSeeder extends Seeder
{
    public function run()
    {
        $accounts = Account::all();
        $services = ApiService::all();
        $tokenTypes = TokenType::all();

        // Создаем токены для каждого аккаунта и сервиса
        foreach ($accounts as $account) {
            foreach ($services as $service) {
                $supportedTypes = $service->tokenTypes;

                if ($supportedTypes->isNotEmpty()) {
                    Token::create([
                        'account_id' => $account->id,
                        'api_service_id' => $service->id,
                        'token_type_id' => $supportedTypes->first()->id,
                        'access_token' => 'test-token-' . $account->id . '-' . $service->id,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
