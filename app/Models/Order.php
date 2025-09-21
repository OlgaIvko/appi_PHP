<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'odid',
        'date',
        'last_change_date',
        'warehouse_name',
        'country_name',
        'oblast_okrug_name',
        'region_name',
        'supplier_article',
        'nm_id',
        'barcode',
        'category',
        'subject',
        'brand',
        'tech_size',
        'income_id',
        'is_supply',
        'is_realization',
        'total_price',
        'discount_percent',
        'spp',
        'finished_price',
        'price_with_disc',
        'is_cancel',
        'cancel_date',
        'order_type',
        'sticker',
        'g_number',
        'last_updated'
    ];

    protected $casts = [
        'date' => 'datetime',
        'last_change_date' => 'datetime',
        'cancel_date' => 'datetime',
        'is_supply' => 'boolean',
        'is_realization' => 'boolean',
        'is_cancel' => 'boolean',
        'total_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'spp' => 'decimal:2',
        'finished_price' => 'decimal:2',
        'price_with_disc' => 'decimal:2',
        'last_updated' => 'datetime'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeFreshData($query, $days = 7, $accountId = null)
    {
        $query = $query->where('date', '>=', now()->subDays($days));

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        return $query;
    }

    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    // Добавим scope для свежих данных по дате
    public function scopeFreshByDate($query, $days = 7, $accountId = null)
    {
        $query = $query->where('date', '>=', now()->subDays($days));

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        return $query->orderBy('date', 'desc');
    }

    public function scopeToday($query, $accountId = null)
    {
        $query = $query->whereDate('date', today());

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        return $query;
    }

    public function scopeThisWeek($query, $accountId = null)
    {
        $query = $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        return $query;
    }

    public function scopeThisMonth($query, $accountId = null)
    {
        $query = $query->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        return $query;
    }
}
