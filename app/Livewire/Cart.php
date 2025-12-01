<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

final class Cart extends Component
{
    public array $cartItems = [];

    public function mount(): void
    {
        $this->cartItems = Session::get('cart', []);
    }

    public function removeItem(int $index): void
    {
        if (isset($this->cartItems[$index])) {
            unset($this->cartItems[$index]);
            $this->cartItems = array_values($this->cartItems);
        }
    }

    #[On('removeCartItem')]
    public function updateCartItems(): void
    {
        $this->cartItems = Session::get('cart', []);
    }

    public function getSubtotalProperty(): float
    {
        $productIds = array_column($this->cartItems, 'product_id');
        $products = Product::whereIn('id', $productIds)->get();
        $product = collect($this->cartItems)->keyBy('product_id');

        return $products->sum(fn ($item) => $item->purchase_currency_price * $product[$item->id]['quantity']);
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
        return collect($this->cartItems)->sum('quantity');
    }

    #[On('removeCartItem')]
    public function render(): Factory|View
    {
        return view('livewire.cart');
    }
}
