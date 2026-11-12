<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Filament\Notifications\Notification;
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
        $this->saveQuantity($cartService, $this->quantity + $this->product->orderQuantityStep());
    }

    /**
     * A quantity typed into the cart is rounded up to the next orderable one
     * (a whole number of order units, at least the minimum), and cut to the
     * stock on hand.
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
        if (! $cartService->updateItem($this->product->id, $quantity)) {
            Notification::make()
                ->title('Nincs ennyi raktáron.')
                ->body($this->product->stockLimitMessage())
                ->warning()
                ->send();
        }

        $this->quantity = $cartService->getItem($this->product->id)->quantity ?? $this->product->orderableQuantity($quantity);
        $this->dispatch('cartUpdated');
    }
}
