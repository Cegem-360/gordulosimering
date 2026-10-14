<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Livewire\Concerns\AddsToQuoteList;
use App\Models\Product;
use App\Services\CartService;
use App\Services\ProductRecommendations;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class Show extends Component
{
    use AddsToQuoteList;

    #[Locked]
    public Product $product;

    public int $quantity;

    public function addToCart(CartService $cartService): void
    {
        if (! $this->product->isInStock()) {
            return;
        }

        $cartService->addItem($this->product->id, $this->quantity);

        $this->dispatch('cartUpdated');

        Notification::make()
            ->title('A termék sikeresen hozzáadva a kosárhoz.')
            ->success()
            ->send();
    }

    public function mount(Product $product): void
    {
        abort_unless($product->is_web_visible === true, 404);

        $this->quantity = $product->minimumOrderQuantity();
    }

    public function increment(): void
    {
        $this->quantity = $this->product->orderableQuantity($this->quantity + $this->product->orderQuantityStep());
    }

    public function decrement(): void
    {
        $lower = $this->quantity - $this->product->orderQuantityStep();

        $this->quantity = $this->product->orderableQuantity(max($lower, $this->product->minimumOrderQuantity()));
    }

    public function updatedQuantity(): void
    {
        $this->quantity = $this->product->orderableQuantity($this->quantity);
    }

    /**
     * The "Gyakran együtt vásárolják" row.
     *
     * @return Collection<int, Product>
     */
    #[Computed]
    public function recommendations(): Collection
    {
        return resolve(ProductRecommendations::class)->for($this->product);
    }

    public function render(): Factory|View
    {
        $this->quantity = $this->product->orderableQuantity($this->quantity);

        return view('livewire.products.show');
    }

    protected function quoteQuantity(): int
    {
        return max(1, $this->quantity);
    }
}
