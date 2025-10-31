<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents an individual item within an order.
 *
 * Each OrderItem belongs to one Order and references a single Product.
 * Stores the quantity, unit price, and subtotal for that product in the order.
 */
class OrderItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'qty',
        'unit_price',
        'subtotal'
    ];
    
    /**
     * Define the relationship between OrderItem and Product.
     *
     * @return BelongsTo<Product>
     */
    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
