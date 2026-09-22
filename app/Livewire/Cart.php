<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\CartService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
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

    #[Computed]
    public function subtotal(): float
    {
        return $this->cartItems->sum(fn ($item): int|float => $item->product->net_selling_price * $item->quantity);
    }

    #[Computed]
    public function vatAmount(): float
    {
        return $this->subtotal * 0.27;
    }

    #[Computed]
    public function total(): float
    {
        return $this->subtotal + $this->vatAmount;
    }

    #[Computed]
    public function itemCount(): int
    {
        return $this->cartItems->sum('quantity');
    }

    public function render(): Factory|View
    {
        return view('livewire.cart');
    }
}
