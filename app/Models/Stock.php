<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'nm_id',
        'warehouse',
        'quantity',
        'in_way_to_client',
        'in_way_from_client'
    ];



    public function account()
    {
        return $this->belongsTo(Account::class);
    }

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
}
