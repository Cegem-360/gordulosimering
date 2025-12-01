<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class LiveSearch extends Component
{
    public string $query = '';

    public bool $showResults = false;

    #[Computed]
    public function results(): Collection
    {
        if (mb_strlen($this->query) < 2) {
            return collect();
        }

        return Product::query()
            ->select(['id', 'name', 'slug', 'product_code', 'net_selling_price', 'images', 'minimum_stock'])
            ->where(function ($q) {
                $q->where('product_code', 'LIKE', $this->query . '%')
                    ->orWhere('product_code', 'LIKE', '%' . $this->query . '%')
                    ->orWhere('name', 'LIKE', '%' . $this->query . '%');
            })
            ->orderByRaw('CASE WHEN product_code LIKE ? THEN 0 ELSE 1 END', [$this->query . '%'])
            ->limit(8)
            ->get();
    }

    public function updatedQuery(): void
    {
        $this->showResults = mb_strlen($this->query) >= 2;
    }

    public function hideResults(): void
    {
        $this->showResults = false;
    }

    public function showResultsPanel(): void
    {
        if (mb_strlen($this->query) >= 2) {
            $this->showResults = true;
        }
    }

    public function goToProduct(string $slug): void
    {
        $this->redirect(route('products.show', $slug));
    }

    public function search(): void
    {
        if (mb_strlen($this->query) >= 2) {
            $this->redirect(route('products.index', ['search' => $this->query]));
        }
    }

    public function render(): View
    {
        return view('livewire.live-search');
    }
}
