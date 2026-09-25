<?php

declare(strict_types=1);

namespace App\Livewire\Products\Categories;

use App\Livewire\Concerns\FiltersProducts;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

final class Index extends Component
{
    use FiltersProducts;
    use WithPagination;

    public function clearFilters(): void
    {
        $this->resetProductFilters();
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $query = $this->filterableProducts();
        $this->applySelectedFilters($query);

        return $query->paginate(24);
    }

    public function render(): Factory|View
    {
        return view('livewire.products.categories.index', [
            'filters' => $this->filters,
            'products' => $this->products,
        ]);
    }

    /**
     * @return Builder<Product>
     */
    protected function filterableProducts(): Builder
    {
        return Product::query()->webVisible();
    }
}
