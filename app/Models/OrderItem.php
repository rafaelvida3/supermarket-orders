<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        "order_id",
        "product_id",
        "qty",
        "unit_price",
        "subtotal",
    ];

    protected $casts = [
        "qty" => "integer",
        "unit_price" => "decimal:2",
        "subtotal" => "decimal:2",
    ];

    /**
     * @return BelongsTo<Product, OrderItem>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
