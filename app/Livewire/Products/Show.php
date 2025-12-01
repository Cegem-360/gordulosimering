<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Models\Product;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class Show extends Component
{
    #[Locked]
    public Product $product;

    public int $quantity;

    public function addToCart(): void
    {
        $cart = Session::get('cart', []);

        $existingIndex = collect($cart)->search(fn ($item) => $item['product_id'] === $this->product->id);

        if ($existingIndex !== false) {
            $cart[$existingIndex]['quantity'] += $this->quantity;
        } else {
            $cart['product_id'] = [
                'product_id' => $this->product->id,
                'quantity' => $this->quantity,
            ];
        }

        Session::put('cart', $cart);

        Notification::make()
            ->title('A termék sikeresen hozzáadva a kosárhoz.')
            ->success()
            ->send();
    }

    public function mount(Product $product): void
    {
        $this->quantity = $product->min_order_quantity <= 1 ? 1 : $product->min_order_quantity;
    }

    public function increment(): void
    {
        $this->quantity++;
    }

    public function decrement(): void
    {
        $min = $this->product->min_order_quantity >= 1 ? $this->product->min_order_quantity : 1;

        if ($this->quantity > $min) {
            $this->quantity--;
        }
    }

    public function updatedQuantity(): void
    {
        $min = $this->product->min_order_quantity >= 1 ? $this->product->min_order_quantity : 1;

        if ($this->quantity < $min) {
            $this->quantity = $min;
        }
    }

    public function render(): Factory|View
    {
        if ($this->quantity < $this->product->min_order_quantity) {
            $this->quantity = $this->product->min_order_quantity;
        }

        return view('livewire.products.show');
    }
}
