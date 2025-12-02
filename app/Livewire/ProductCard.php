<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class ProductCard extends Component
{
    #[Locked]
    public Product $product;

    public function addToCart(CartService $cartService): void
    {
        $quantity = $this->product->min_order_quantity >= 1 ? $this->product->min_order_quantity : 1;

        $cartService->addItem($this->product->id, $quantity);

        $this->dispatch('cartUpdated');

        Notification::make()
            ->title('A termék sikeresen hozzáadva a kosárhoz.')
            ->success()
            ->send();
    }

    public function render(): Factory|View
    {
        return view('livewire.product-card');
    }
}
