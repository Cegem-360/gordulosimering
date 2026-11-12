<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

final readonly class CartService
{
    private Cart $cart;

    public function __construct()
    {
        $this->cart = Cart::query()->firstOrCreate(['user_id' => Auth::id()]);
    }

    /**
     * Quantities are rounded up to an orderable amount here too, so the
     * minimum and the order unit hold whatever the page sent, and never go
     * above the stock (the rest can only be asked for in a quote).
     *
     * @return bool whether the whole quantity fit; false when it was cut to the stock
     */
    public function addItem($productId, $quantity): bool
    {
        $cartItem = $this->cart->cartItems()->where('product_id', $productId)->first();

        if ($cartItem) {
            return $this->updateItem($productId, $cartItem->quantity + $quantity);
        }

        $product = Product::query()->find($productId);

        if (! $product) {
            return false;
        }

        $requested = $product->orderableQuantity((int) $quantity);
        $quantity = min($requested, $product->maximumCartQuantity());

        if ($quantity < $product->minimumOrderQuantity()) {
            return false;
        }

        $this->cart->cartItems()->create(['product_id' => $productId, 'quantity' => $quantity]);

        return $quantity === $requested;
    }

    /**
     * @return bool whether the whole quantity fit; false when it was cut to the stock
     */
    public function updateItem($productId, $quantity): bool
    {
        $cartItem = $this->cart->cartItems()->where('product_id', $productId)->first();

        if (! $cartItem) {
            return false;
        }

        $requested = $cartItem->product->orderableQuantity((int) $quantity);
        $maximum = $cartItem->product->maximumCartQuantity();

        if ($maximum < $cartItem->product->minimumOrderQuantity()) {
            return false;
        }

        $cartItem->quantity = min($requested, $maximum);
        $cartItem->save();

        return $cartItem->quantity === $requested;
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
        return $this->cart->cartItems->sum(fn ($item): int|float => $item->product->unit_price * $item->quantity);
    }

    public function getItem(int $productId): ?CartItem
    {
        return $this->cart->cartItems()->where('product_id', $productId)->first();
    }
}
