<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final class CartService
{
    private Cart $cart;

    public function __construct()
    {
        $userId = Auth::id();
        if (Auth::check()) {
            $this->cart = Cart::firstOrCreate(['user_id' => $userId]);
        } else {
            $sessionId = Session::getId();
            $this->cart = Cart::firstOrCreate(['session_id' => $sessionId]);
        }
    }

    public function addItem($productId, $quantity): void
    {

        $cartItem = $this->cart->cartItems()->where('product_id', $productId)->first();

        if ($cartItem) {
            $this->updateItem($productId, $cartItem->quantity + $quantity);
        } else {
            $this->cart->cartItems()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }
    }

    public function updateItem($productId, $quantity): void
    {
        $cartItem = $this->cart->items()->where('product_id', $productId)->first();

        if ($cartItem) {
            if ($quantity <= $cartItem->product->all_quantity) {
                $cartItem->quantity = $quantity;
            }

            $cartItem->save();
        }
    }

    public function removeItem($productId): void
    {
        $this->cart->cartItems()->where('product_id', $productId)->delete();
    }

    public function clearCart(): void
    {
        $this->cart->cartItems()->delete();
    }

    public function getCartItems()
    {
        return $this->cart->cartItems;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getTotal()
    {
        return $this->cart->cartItems->sum(function ($item): int|float {
            return $item->product->net_retail_price * $item->quantity;
        });
    }

    public function getItem(int $productId): ?CartItem
    {
        return $this->cart->cartItems()->where('product_id', $productId)->first();
    }
}
