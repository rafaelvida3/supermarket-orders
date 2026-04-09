<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'id',
        'name',
        'price',
        'qty_stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'qty_stock' => 'integer',
    ];
}
