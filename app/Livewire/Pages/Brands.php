<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

/**
 * The Márkáink page: the brands the shop carries (config/brands.php), each
 * logo opening the products of that brand, by the brand filter or, where the
 * ERP data has no brand value, by a name search.
 */
final class Brands extends Component
{
    public function render(): Factory|View
    {
        $counts = $this->productCounts();

        return view('livewire.pages.brands', [
            'brands' => collect(config('brands'))->map(function (array $brand) use ($counts): array {
                $count = $counts[$brand['name']] ?? 0;

                return [
                    ...$brand,
                    'product_count' => $count,
                    'url' => match (true) {
                        $count === 0 => null,
                        isset($brand['filter']) => route('categories.index', ['marka' => $brand['filter']]),
                        default => route('products.index', ['search' => $brand['search']]),
                    },
                ];
            })->all(),
        ]);
    }

    /**
     * Web-visible products per brand name. The name searches scan every
     * product, so the counts are kept for an hour.
     *
     * @return array<string, int>
     */
    private function productCounts(): array
    {
        return Cache::remember('brands-page-product-counts', now()->addHour(), function (): array {
            $brands = collect(config('brands'));

            $byBrand = Product::query()
                ->webVisible()
                ->whereIn('brand', $brands->pluck('filter')->filter())
                ->selectRaw('brand, count(*) as aggregate')
                ->groupBy('brand')
                ->pluck('aggregate', 'brand');

            return $brands->mapWithKeys(fn (array $brand): array => [$brand['name'] => (int) match (true) {
                isset($brand['filter']) => $byBrand[$brand['filter']] ?? 0,
                isset($brand['search']) => Product::query()->webVisible()->matchingSearch($brand['search'])->count(),
                default => 0,
            }])->all();
        });
    }
}
