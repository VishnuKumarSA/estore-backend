<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        "user_id",
        "order_number",
        "total_amount",
        "tax",
        "shipping_charge",
        "discount",
        "grand_total",
        "payment_method",
        "payment_status",
        "order_status",
        "shipping_address",
        "billing_address"
    ];
}
