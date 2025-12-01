<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

final class CartIcon extends Component
{
    public int $itemCount = 0;

    public float $total = 0;

    public function mount(CartService $cartService): void
    {
        $this->updateCart($cartService);
    }

    #[On('cartUpdated')]
    public function refreshCart(CartService $cartService): void
    {
        $this->updateCart($cartService);
    }

    public function render(): View
    {
        return view('livewire.cart-icon');
    }

    private function updateCart(CartService $cartService): void
    {
        $cartItems = $cartService->getCartItems();
        $this->itemCount = $cartItems->sum('quantity');
        $this->total = $cartItems->sum(fn ($item) => $item->product->net_selling_price * $item->quantity);
    }
}
