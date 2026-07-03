<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = auth()->id();
        $allOrder = Order::where('user_id', $user_id)->get();
        return response()->json(['message' => 'Order list fetched successfully', 'order' => $allOrder], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
        ]);

        $user_id = auth()->id();

        $lastOrder = Order::latest('id')->first();

        $orderNumber = 'ORD-' . date('Y') . '-' . str_pad(
            ($lastOrder ? $lastOrder->id + 1 : 1),
            5,
            '0',
            STR_PAD_LEFT
        );

        $cart = Cart::where('user_id', $user_id)->where('status', 'active')->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], 400);
        }
        $cartItems = CartItem::where('cart_id', $cart->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }
        foreach ($cartItems as $item) {
            $product = Product::find($item->product_id);
            if ($product->stock < $item->quantity) {
                return response()->json([
                    'message' => $product->name . ' is out of stock.'
                ], 400);
            }
        }

        $total_amount = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
        $tax = 0;
        $shipping_charge = 0;
        $discount = 0;
        $grand_total = $total_amount + $tax + $shipping_charge - $discount;

        DB::beginTransaction();

        try {

            $order = Order::create([
                'user_id' => $user_id,
                'order_number' => $orderNumber,
                'total_amount' => $total_amount,
                'tax' => $tax,
                'shipping_charge' => $shipping_charge,
                'discount' => $discount,
                'grand_total' => $grand_total,
                'payment_method' => $request->payment_method,
                'payment_status' => 'Pending',
                'order_status' => 'Pending',
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address
            ]);

            foreach ($cartItems as $item) {
                $sub_total = $item->price * $item->quantity;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $sub_total,
                ]);

                $product = Product::find($item->product_id);
                $product->decrement('stock', $item->quantity);
            }

            CartItem::where('cart_id', $cart->id)->delete();
            $cart->update(['status' => 'ordered']);

            DB::commit();

            return response()->json(['message' => 'Order placed successfully', 'order' => $order], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        if ($order->user_id != auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $order->load('orderItems.product');

        return response()->json([
            'message' => 'Order fetched successfully',
            'order' => $order
        ]);
    }

    public function order_status(Order $order, Request $request)
    {
        $request->validate([
            'order_status' => 'required|in:Pending,Confirmed,Processing,Shipped,Delivered,Cancelled'
        ]);

        $order->update(['order_status' => $request->order_status]);

        return response()->json(['message' => 'Order status update successfully', 'order' => $order], 200);
    }

    public function payment_status(Order $order, Request $request)
    {
        $request->validate([
            'payment_status' => 'required|in:Pending,Paid,Failed,Refunded'
        ]);

        $order->update(['payment_status' => $request->payment_status]);

        return response()->json(['message' => 'Order payment status update successfully', 'order' => $order], 200);
    }
}
