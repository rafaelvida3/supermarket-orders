<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a customer order.
 *
 * Each order belongs to one customer and contains multiple order items.
 * Provides relationship access to its associated OrderItem models.
 */
class Order extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_name',
        'delivery_date',
        'total'
    ];

    /**
     * Define the one-to-many relationship between Order and OrderItem.
     *
     * @return HasMany<OrderItem>
     */
    public function items(): HasMany {
        return $this->hasMany(OrderItem::class);
    }
}
