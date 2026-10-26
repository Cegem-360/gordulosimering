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

    /**
     * A brand of the Márkáink page (config/brands.php) the ERP data has no
     * brand value for, by its name; its logo links here.
     */
    #[Url(as: 'gyarto')]
    public string $brandName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBrandName(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->resetProductFilters();
        $this->search = '';
        $this->brandName = '';
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

        if ($this->catalogBrand !== null || $this->isListingABrand()) {
            return $query->orderByProductCode()->paginate(24);
        }

        $query->inStockFirst();

        if (mb_strlen($this->search) >= 2) {
            $query->orderBySearchRelevance($this->search);
        }

        return $query->paginate(24);
    }

    /**
     * @return array{name: string, logo: string, filter?: string, name_word?: string, code_part?: string}|null
     */
    #[Computed]
    public function catalogBrand(): ?array
    {
        return $this->brandName === '' ? null : collect(config('brands'))->firstWhere('name', $this->brandName);
    }

    public function render(): View
    {
        return view('livewire.products.index', [
            'filters' => $this->filters,
            'products' => $this->products,
        ]);
    }

    /**
     * The web-visible products, narrowed by the brand from the Márkáink page
     * and by the search term once it has two characters. The filter counts
     * follow them too.
     *
     * @return Builder<Product>
     */
    protected function filterableProducts(): Builder
    {
        return Product::query()
            ->webVisible()
            ->when($this->catalogBrand, fn (Builder $query, array $brand) => $query->ofCatalogBrand($brand))
            ->when(mb_strlen($this->search) >= 2, fn (Builder $query) => $query->matchingSearch($this->search));
    }
}
