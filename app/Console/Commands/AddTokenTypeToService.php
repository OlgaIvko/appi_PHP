<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApiService;
use App\Models\TokenType;

class AddTokenTypeToService extends Command
{
    protected $signature = 'add:token-type-to-service {service_id} {token_type_id}';
    protected $description = 'Add token type to API service';

    public function handle()
    {
        $service = ApiService::find($this->argument('service_id'));
        $tokenType = TokenType::find($this->argument('token_type_id'));

        if (!$service) {
            $this->error("❌ API Service not found with ID: {$this->argument('service_id')}");
            return 1;
        }

        if (!$tokenType) {
            $this->error("❌ Token Type not found with ID: {$this->argument('token_type_id')}");
            return 1;
        }

        $service->tokenTypes()->attach($tokenType);

        $this->info("✅ Token type '{$tokenType->name}' added to service '{$service->name}'");
        return 0;
    }
}
