<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a product available in the system.
 *
 * Each product contains basic information such as name, price, and stock quantity.
 * Used in order creation and inventory management.
 */
class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'price',
        'qty_stock',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'qty_stock' => 'integer',
    ];
}
