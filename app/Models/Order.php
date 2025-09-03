<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'g_number'
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
        'price_with_disc' => 'decimal:2'
    ];
}
