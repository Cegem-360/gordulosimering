<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Product;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * The Márkáink page: the brands the shop carries, each logo opening the
 * product list filtered by that brand.
 */
final class Brands extends Component
{
    public function render(): Factory|View
    {
        $brands = collect(config('brands'));

        $productCounts = Product::query()
            ->webVisible()
            ->whereIn('brand', $brands->pluck('filter')->filter())
            ->selectRaw('brand, count(*) as aggregate')
            ->groupBy('brand')
            ->pluck('aggregate', 'brand');

        return view('livewire.pages.brands', [
            'brands' => $brands->map(fn (array $brand): array => [
                ...$brand,
                'product_count' => (int) ($productCounts[$brand['filter'] ?? ''] ?? 0),
            ])->all(),
        ]);
    }
}
