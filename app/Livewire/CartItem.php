<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CartItem extends Component
{
    public Product $product;

    public int $quantity;

    public function mount(int $productId, int $quantity): void
    {
        $this->product = Product::query()->findOrFail($productId);
        $this->quantity = $quantity;
    }

    public function decreaseQuantity(CartService $cartService): void
    {
        $lower = $this->quantity - $this->product->orderQuantityStep();

        if ($lower >= $this->product->minimumOrderQuantity()) {
            $this->saveQuantity($cartService, $lower);
        }
    }

    public function increaseQuantity(CartService $cartService): void
    {
        $maxQuantity = $this->product->maximum_stock ?: 9999;
        $higher = $this->quantity + $this->product->orderQuantityStep();

        if ($higher <= $maxQuantity) {
            $this->saveQuantity($cartService, $higher);
        }
    }

    /**
     * A quantity typed into the cart is rounded up to the next orderable one
     * (a whole number of order units, at least the minimum).
     */
    public function updatedQuantity(CartService $cartService): void
    {
        $this->saveQuantity($cartService, $this->quantity);
    }

    public function removeProduct(CartService $cartService): void
    {
        $cartService->removeItem($this->product->id);
        $this->dispatch('cartUpdated');
    }

    public function render(): Factory|View
    {
        return view('livewire.cart-item');
    }

    private function saveQuantity(CartService $cartService, int $quantity): void
    {
        $this->quantity = $this->product->orderableQuantity($quantity);
        $cartService->updateItem($this->product->id, $this->quantity);
        $this->dispatch('cartUpdated');
    }
}
