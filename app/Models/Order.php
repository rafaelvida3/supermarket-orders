<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        "customer_name",
        "delivery_date",
        "total",
    ];

    protected $casts = [
        "delivery_date" => "date",
        "total" => "decimal:2",
    ];

    /**
     * @return HasMany<OrderItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
