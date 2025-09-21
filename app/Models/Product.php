<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'nm_id',
        'name',
        'brand',
        'price',
        'discount',
        'sale_price',
        'images',
        'last_updated'
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'integer',
        'discount' => 'integer',
        'sale_price' => 'integer',
        'last_updated' => 'datetime'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeFreshData($query, $hours = 24, $accountId = null)
    {
        $query = $query->where('last_updated', '>=', now()->subHours($hours));

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        return $query;
    }
}
