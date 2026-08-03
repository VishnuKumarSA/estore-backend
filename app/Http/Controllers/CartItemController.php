<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CartItem $cartItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CartItem $cartItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($cartItem->product_id);

        if ($request->quantity > $product->stock) {
            return response()->json([
                'message' => 'Requested quantity exceeds available stock.'
            ], 400);
        }

        $cartItem->update([
            'quantity' => $request->quantity
        ]);

        $cart = $cartItem->cart->load('cartItems.product');
        $subtotal = $cart->cartItems->sum(fn($item) => $item->product->price * $item->quantity);
        $total = $subtotal;

        return response()->json([
            'message' => 'Cart item updated successfully',
            'cart_item' => $cartItem->fresh(),
            'subtotal' => $subtotal,
            'total' => $total
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        $cart = $cartItem->cart;
        $cartItem->delete();

        $cart->load('cartItems.product');
        $subtotal = $cart->cartItems->sum(fn($item) => $item->price * $item->quantity);
        $total = $subtotal;

        return response()->json([
            'message' => 'Cart item deleted successfully',
            'subtotal' => $subtotal,
            'total' => $total,
        ], 200);
    }
}
