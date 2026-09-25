<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Livewire\Concerns\FiltersProducts;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class Index extends Component
{
    use FiltersProducts;
    use WithPagination;

    #[Url(as: 'search')]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->resetProductFilters();
        $this->search = '';
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

        if (mb_strlen($this->search) >= 2) {
            $query->orderBySearchRelevance($this->search);
        }

        return $query->paginate(24);
    }

    public function render(): View
    {
        return view('livewire.products.index', [
            'filters' => $this->filters,
            'products' => $this->products,
        ]);
    }

    /**
     * The web-visible products, narrowed by the search term once it has two
     * characters. The filter counts follow the search too.
     *
     * @return Builder<Product>
     */
    protected function filterableProducts(): Builder
    {
        return Product::query()
            ->webVisible()
            ->when(mb_strlen($this->search) >= 2, fn (Builder $query) => $query->matchingSearch($this->search));
    }
}
