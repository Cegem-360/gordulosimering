<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Az Integra7-ből frissíti a webshop meglévő termékeit termékkód alapján: a szabad
 * készletet (az összes raktár készlete mínusz a foglalások, legalább nulla) és az
 * ERP-ben vezetett termékadatokat. Új terméket nem hoz létre, mert az Integra nem
 * jelöli, mi szerepeljen a webáruházban; a webshopos adatokhoz (slug, képek,
 * kategóriák, láthatóság) nem nyúl.
 */
final class Integra7Syncer
{
    private const int CHUNK_SIZE = 1000;

    /**
     * @var array<int, string>
     */
    private const array SYNCED_FIELDS = [
        'name', 'size', 'quantity_unit', 'net_selling_price', 'gross_selling_price',
        'is_on_sale', 'sale_percentage', 'is_inactive', 'stock_quantity',
    ];

    /**
     * @return array{updated: int, unchanged: int, missing: int}
     */
    public function sync(bool $dryRun = false): array
    {
        $erpProducts = $this->erpProducts();
        $available = $this->availableQuantities();
        $stats = ['updated' => 0, 'unchanged' => 0, 'missing' => 0];

        Product::query()
            ->select(['id', 'product_code', ...self::SYNCED_FIELDS])
            ->chunkById(self::CHUNK_SIZE, function (Collection $products) use ($erpProducts, $available, $dryRun, &$stats): void {
                foreach ($products as $product) {
                    $code = mb_trim((string) $product->product_code);
                    $erpProduct = $erpProducts->get($code);

                    if ($erpProduct === null) {
                        $stats['missing']++;
                    }

                    $product->fill([
                        ...($erpProduct === null ? [] : $this->productAttributes($erpProduct)),
                        'stock_quantity' => max(0.0, $available->get($code, 0.0)),
                    ]);

                    if (! $product->isDirty()) {
                        $stats['unchanged']++;

                        continue;
                    }

                    if (! $dryRun) {
                        $product->save();
                    }

                    $stats['updated']++;
                }
            });

        return $stats;
    }

    /**
     * @return array<string, mixed>
     */
    private function productAttributes(object $erpProduct): array
    {
        $netPrice = round((float) $erpProduct->nettoprice, 2);

        return [
            'name' => mb_trim((string) $erpProduct->name),
            'size' => mb_trim((string) $erpProduct->size) ?: null,
            'quantity_unit' => mb_trim((string) $erpProduct->quantity_unit) ?: null,
            'net_selling_price' => $netPrice,
            'gross_selling_price' => round($netPrice * (1 + (float) $erpProduct->taxpercent / 100), 2),
            'is_on_sale' => (bool) $erpProduct->is_discount,
            'sale_percentage' => round((float) $erpProduct->discountpercent, 2),
            'is_inactive' => (bool) $erpProduct->inactive,
        ];
    }

    /**
     * @return Collection<string, object>
     */
    private function erpProducts(): Collection
    {
        return DB::connection('integra7')
            ->table('products')
            ->get(['code', 'name', 'size', 'quantity_unit', 'nettoprice', 'taxpercent', 'is_discount', 'discountpercent', 'inactive'])
            ->keyBy(fn (object $row): string => mb_trim((string) $row->code));
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
