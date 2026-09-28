<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Az Integra7 raktárkészletéből frissíti a termékek szabad készletét: termékkódonként
 * az összes raktár készlete mínusz a foglalások. Az Integrában nem szereplő vagy
 * negatív készletű termék készlete nulla.
 */
final class StockSyncer
{
    private const int CHUNK_SIZE = 1000;

    /**
     * @return array{updated: int, unchanged: int, missing: int}
     */
    public function sync(bool $dryRun = false): array
    {
        $available = $this->availableQuantities();
        $stats = ['updated' => 0, 'unchanged' => 0, 'missing' => 0];

        /** @var array<string, array<int, int>> $idsByQuantity */
        $idsByQuantity = [];

        Product::query()
            ->select(['id', 'product_code', 'stock_quantity'])
            ->chunkById(self::CHUNK_SIZE, function (Collection $products) use ($available, &$stats, &$idsByQuantity): void {
                foreach ($products as $product) {
                    $code = mb_trim((string) $product->product_code);

                    if (! $available->has($code)) {
                        $stats['missing']++;
                    }

                    $quantity = max(0.0, (float) $available->get($code, 0));

                    if ((float) $product->stock_quantity === $quantity) {
                        $stats['unchanged']++;

                        continue;
                    }

                    $idsByQuantity[(string) $quantity][] = $product->id;
                    $stats['updated']++;
                }
            });

        if (! $dryRun) {
            foreach ($idsByQuantity as $quantity => $ids) {
                foreach (array_chunk($ids, self::CHUNK_SIZE) as $chunk) {
                    Product::query()->whereKey($chunk)->update(['stock_quantity' => $quantity]);
                }
            }
        }

        return $stats;
    }

    /**
     * @return Collection<string, float>
     */
    private function availableQuantities(): Collection
    {
        return DB::connection('integra7')
            ->table('quantities')
            ->selectRaw('product_code, SUM(quantity - reserved) AS available')
            ->groupBy('product_code')
            ->get()
            ->mapWithKeys(fn (object $row): array => [mb_trim((string) $row->product_code) => (float) $row->available]);
    }
}
