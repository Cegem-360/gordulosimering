<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

final class CartService
{
    private Cart $cart;

    public function __construct()
    {
        $this->cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
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
        $cartItem = $this->cart->cartItems()->where('product_id', $productId)->first();

        if ($cartItem) {
            $maxStock = $cartItem->product->maximum_stock ?: 9999;

            if ($quantity <= $maxStock) {
                $cartItem->quantity = $quantity;
                $cartItem->save();
            }
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

    public function getCartItems(): Collection
    {
        return $this->cart->cartItems()->with('product')->get();
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getTotal(): int|float
    {
        return $this->cart->cartItems->sum(function ($item): int|float {
            return $item->product->net_selling_price * $item->quantity;
        });
    }

    public function getItem(int $productId): ?CartItem
    {
        return $this->cart->cartItems()->where('product_id', $productId)->first();
    }
}
