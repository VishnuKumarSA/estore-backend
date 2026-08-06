<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product;
class OrderItem extends Model
{
    protected $fillable = [
        "order_id",
        "product_id",
        "quantity",
        "price",
        "subtotal"
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
}
