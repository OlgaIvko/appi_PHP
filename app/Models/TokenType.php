<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use app\Models\ApiService;

class TokenType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    public function apiServices()
    {
        return $this->belongsToMany(ApiService::class, 'api_service_token_types');
    }
}
