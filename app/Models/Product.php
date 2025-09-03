<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'nm_id',
        'name',
        'brand',
        'price',
        'discount',
        'sale_price',
        'images'
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'integer',
        'discount' => 'integer',
        'sale_price' => 'integer'
    ];
}
