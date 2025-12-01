<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\CartService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

final class Cart extends Component
{
    public Collection $cartItems;

    public function mount(CartService $cartService): void
    {
        $this->cartItems = $cartService->getCartItems();
    }

    public function removeItem(int $productId, CartService $cartService): void
    {
        $cartService->removeItem($productId);
        $this->cartItems = $cartService->getCartItems();
    }

    #[On('cartUpdated')]
    public function refreshCart(CartService $cartService): void
    {
        $this->cartItems = $cartService->getCartItems();
    }

    public function getSubtotalProperty(): float
    {
        return $this->cartItems->sum(fn ($item) => $item->product->net_selling_price * $item->quantity);
    }

    public function getVatAmountProperty(): float
    {
        return $this->subtotal * 0.27;
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->vatAmount;
    }

    public function getItemCountProperty(): int
    {
        return $this->cartItems->sum('quantity');
    }

    public function render(): Factory|View
    {
        return view('livewire.cart');
    }
}
