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

        if ($request->quantity <= 0) {
            return response()->json([
                'message' => 'Quantity must be greater than 0.'
            ], 400);
        }

        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Requested quantity exceeds available stock'
            ], 400);
        }

        $cart = Cart::firstOrCreate(
            [
                'user_id' => $user_id,
                'status' => 'active'
            ]
        );

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        $currentQty = $cartItem ? $cartItem->quantity : 0;

        $totalQty = $currentQty + $request->quantity;

        if ($totalQty > $product->stock) {
            return response()->json([
                'message' => 'Requested quantity exceeds available stock.'
            ], 400);
        }

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $totalQty,
                'price' => $product->price
            ]);
        }

        $cartItemCount = Cart::where('id', $cart->id)->count();

        return response()->json([
            'message' => 'Cart added successfully',
            'cartItemCount' => $cartItemCount
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

    public function getCartCount()
    {
        $user_id = auth()->id();
        $cartItemCount = Cart::where('user_id', $user_id)->where('status', 'active')->count();
        return response()->json([
            'message' => 'Cart count fetched',
            'cartItemCount' => $cartItemCount
        ], 200);
    }
}
