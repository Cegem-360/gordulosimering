<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Livewire\Concerns\FiltersProducts;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The Akciók page: the web-visible products the ERP marks as on sale, with
 * the usual filter sidebar. In-stock products with a photo come first, then
 * the biggest discount, so the top of the list is something one can buy. Customer
 * discounts are left out: they apply to every product for a logged-in
 * customer, so they would turn the page into the whole catalogue.
 */
final class Sale extends Component
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

        return $query
            ->inStockFirst()
            ->orderByRaw('CASE WHEN featured_image IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('sale_percentage')
            ->orderBy('name')
            ->paginate(24);
    }

    public function render(): View
    {
        return view('livewire.products.categories.index', [
            'filters' => $this->filters,
            'products' => $this->products,
            'heading' => 'Akciók',
            'breadcrumb' => 'Akciók',
        ]);
    }

    /**
     * @return Builder<Product>
     */
    protected function filterableProducts(): Builder
    {
        return Product::query()->webVisible()->where('is_on_sale', true);
    }
}
