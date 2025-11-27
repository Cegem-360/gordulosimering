<?php

declare(strict_types=1);

namespace App\Livewire\Products\Categories;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

final class Index extends Component
{
    use WithPagination;

    /** @var array<string, array<int, string>> */
    public array $selectedFilters = [
        'product_variety' => [],
        'size' => [],
        'quality' => [],
        'stock' => [],
    ];

    public function updatedSelectedFilters(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->selectedFilters = [
            'product_variety' => [],
            'size' => [],
            'quality' => [],
            'stock' => [],
        ];
        $this->resetPage();
    }

    /**
     * @return array<int, array{title: string, key: string, items: array<int, array{name: string, value: string, count: int}>}>
     */
    public function getFiltersProperty(): array
    {
        return [
            [
                'title' => 'Kategória',
                'key' => 'product_variety',
                'items' => $this->getFilterOptions('product_variety', 10),
            ],
            [
                'title' => 'Méret',
                'key' => 'size',
                'items' => $this->getFilterOptions('size', 10),
            ],
            [
                'title' => 'Minőség',
                'key' => 'quality',
                'items' => $this->getFilterOptions('quality', 10),
            ],
            [
                'title' => 'Készlet',
                'key' => 'stock',
                'items' => [
                    ['name' => 'Készleten', 'value' => 'in_stock', 'count' => $this->getInStockCount()],
                    ['name' => 'Rendelésre', 'value' => 'out_of_stock', 'count' => $this->getOutOfStockCount()],
                ],
            ],
        ];
    }

    public function getProductsProperty()
    {
        $query = Product::query();

        // Apply product_variety filter
        if (! empty($this->selectedFilters['product_variety'])) {
            $query->whereIn('product_variety', $this->selectedFilters['product_variety']);
        }

        // Apply size filter
        if (! empty($this->selectedFilters['size'])) {
            $query->whereIn('size', $this->selectedFilters['size']);
        }

        // Apply quality filter
        if (! empty($this->selectedFilters['quality'])) {
            $query->whereIn('quality', $this->selectedFilters['quality']);
        }

        // Apply stock filter
        if (! empty($this->selectedFilters['stock'])) {
            $query->where(function ($q) {
                if (in_array('in_stock', $this->selectedFilters['stock'])) {
                    $q->orWhere('minimum_stock', '>', 0);
                }
                if (in_array('out_of_stock', $this->selectedFilters['stock'])) {
                    $q->orWhere(function ($subQ) {
                        $subQ->whereNull('minimum_stock')
                            ->orWhere('minimum_stock', '<=', 0);
                    });
                }
            });
        }

        return $query->paginate(24);
    }

    public function render()
    {
        return view('livewire.products.categories.index', [
            'filters' => $this->filters,
            'products' => $this->products,
        ]);
    }

    /**
     * @return array<int, array{name: string, value: string, count: int}>
     */
    private function getFilterOptions(string $column, int $limit = 10): array
    {
        return Product::query()
            ->select($column, DB::raw('count(*) as count'))
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'name' => $item->{$column},
                'value' => $item->{$column},
                'count' => $item->count,
            ])
            ->toArray();
    }

    private function getInStockCount(): int
    {
        return Product::query()
            ->where('minimum_stock', '>', 0)
            ->count();
    }

    private function getOutOfStockCount(): int
    {
        return Product::query()
            ->where(function ($query) {
                $query->whereNull('minimum_stock')
                    ->orWhere('minimum_stock', '<=', 0);
            })
            ->count();
    }
}
