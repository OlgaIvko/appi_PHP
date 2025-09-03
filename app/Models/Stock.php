<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'nm_id',
        'warehouse',
        'quantity',
        'in_way_to_client',
        'in_way_from_client'
    ];
}
