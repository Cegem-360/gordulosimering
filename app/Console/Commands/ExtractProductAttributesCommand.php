<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductAttributeExtractor;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

#[Description('A termékek méret-, márka- és anyagadatainak kiszámolása a névből és a méretből')]
#[Signature('app:extract-product-attributes')]
final class ExtractProductAttributesCommand extends Command
{
    private const int CHUNK_SIZE = 1000;

    public function handle(ProductAttributeExtractor $extractor): int
    {
        $updated = 0;

        Product::query()
            ->select(['id', 'name', 'size', ...ProductAttributeExtractor::COLUMNS])
            ->chunkById(self::CHUNK_SIZE, function (Collection $products) use ($extractor, &$updated): void {
                foreach ($products as $product) {
                    $product->fill($extractor->extract($product->name, $product->size));

                    if ($product->isDirty()) {
                        $product->save();
                        $updated++;
                    }
                }
            });

        $this->info("Frissítve: {$updated} termék.");

        return self::SUCCESS;
    }
}
