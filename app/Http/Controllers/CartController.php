<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cart = Cart::with('cartItems.product')
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();
        return response()->json(['message' => 'cart deteils fetched', 'cart' => $cart], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $user_id = auth()->id();

        $product = Product::findOrFail($request->product_id);

        $cart = Cart::firstOrCreate(
            [
                'user_id' => $user_id,
                'status' => 'active'
            ]
        );

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price
            ]);
        }



        return response()->json([
            'message' => 'Cart added successfully'
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Cart $cart)
    {
        if ($cart->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        return response()->json($cart);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cart $cart)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cart $cart)
    {
        
    }
}
