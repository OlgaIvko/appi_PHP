<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'base_url',
        'description',
        'endpoints'
    ];

    protected $casts = [
        'endpoints' => 'array',
        'is_active' => 'boolean'
    ];

    public function tokens()
    {
        return $this->hasMany(Token::class);
    }


    public static function getByName($name)
    {
        return static::where('name', $name)
            ->where('is_active', true)
            ->first();
    }
    public function tokenTypes()
    {
        return $this->belongsToMany(TokenType::class, 'api_service_token_type', 'api_service_id', 'token_type_id')
            ->withTimestamps();
    }
}
