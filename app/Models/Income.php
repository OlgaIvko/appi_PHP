<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'income_id',
        'number',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'quantity',
        'total_price',
        'date_close',
        'warehouse_name',
        'nm_id',
        'status',
        'last_updated'
    ];

    // public function account()
    // {
    //     return $this->belongsTo(Account::class);
    // }

    // public function scopeFreshData($query, $hours = 24, $accountId = null)
    // {
    //     $query = $query->where('last_updated', '>=', now()->subHours($hours));

    //     if ($accountId) {
    //         $query->where('account_id', $accountId);
    //     }

    //     return $query;
    // }

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
