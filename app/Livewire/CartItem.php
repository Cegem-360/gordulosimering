<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;

final class CartItem extends Component
{
    public Product $product;

    public int $quantity;

    public function mount(int $productId, int $quantity): void
    {
        $this->product = Product::findOrFail($productId);
        $this->quantity = $quantity;
    }

    public function decreaseQuantity(CartService $cartService): void
    {
        $minQuantity = $this->product->min_order_quantity ?: 1;

        if ($this->quantity > $minQuantity) {
            $this->quantity--;
            $cartService->updateItem($this->product->id, $this->quantity);
            $this->dispatch('cartUpdated');
        }
    }

    public function increaseQuantity(CartService $cartService): void
    {
        $maxQuantity = $this->product->maximum_stock ?: 9999;

        if ($this->quantity < $maxQuantity) {
            $this->quantity++;
            $cartService->updateItem($this->product->id, $this->quantity);
            $this->dispatch('cartUpdated');
        }
    }

    public function removeProduct(CartService $cartService): void
    {
        $cartService->removeItem($this->product->id);
        $this->dispatch('cartUpdated');
    }

    public function render()
    {
        return view('livewire.cart-item');
    }
}
