<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',

        'first_name',
        'last_name',
        'email',
        'mobile_number',

        'address',
        'city',
        'state',
        'postal_code',

        'subtotal',
        'discount',
        'shipping_charge',
        'tax',
        'grand_total',

        'payment_method',
        'payment_status',
        'order_status',        
    ];
}
