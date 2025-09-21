<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use app\Models\TokenType;

class Token extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'api_service_id',
        'token_type',
        'access_token',
        'refresh_token',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function apiService()
    {
        return $this->belongsTo(ApiService::class);
    }
    public function tokenType()
    {
        return $this->belongsTo(TokenType::class);
    }

    public static function getActiveToken($accountId, $apiServiceId)
    {
        return static::where('account_id', $accountId)
            ->where('api_service_id', $apiServiceId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }
}
