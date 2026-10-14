<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DiscountGroup;
use App\Models\IntegraKnownProduct;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Az Integra7-ből frissíti a webshop meglévő termékeit termékkód alapján: a szabad
 * készletet (az összes raktár készlete mínusz a foglalások, legalább nulla) és az
 * ERP-ben vezetett termékadatokat, köztük a termékcsoportot és a terméktípust. A
 * termékcsoportokat (DiscountGroup) is innen nevezi el. A webshopos adatokhoz
 * (slug, képek, kategóriák, láthatóság) nem nyúl.
 *
 * Az Integra nem jelöli, mi szerepeljen a webáruházban, ezért csak az ERP-ben
 * újonnan megjelenő, aktív kódokból hoz létre terméket, rejtve: az első futás
 * csak megjegyzi a meglévő kódokat (IntegraKnownProduct), így a webshopból
 * szándékosan kihagyott régi termékek nem jönnek be.
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
        'group_code', 'product_variety',
    ];

    /**
     * Az ERP épp újratölti az Integra tábláit; ilyenkor félkész adatot olvasnánk.
     */
    public function isUploadInProgress(): bool
    {
        return (bool) DB::connection('integra7')->table('parameters')->value('upload_progress');
    }

    /**
     * @return array{updated: int, unchanged: int, missing: int, created: int}
     */
    public function sync(bool $dryRun = false): array
    {
        $erpProducts = $this->erpProducts();
        $available = $this->availableQuantities();
        $stats = ['updated' => 0, 'unchanged' => 0, 'missing' => 0, 'created' => 0];

        if (! $dryRun) {
            $this->syncProductGroups();
        }

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

        $stats['created'] = $this->createNewProducts($erpProducts, $available, $dryRun);

        return $stats;
    }

    /**
     * Az Integrában először látott, aktív kódokból rejtett terméket hoz létre,
     * majd az összes új kódot ismertnek jelöli. Ha még nincs ismert kód (első
     * futás), csak megjegyzi őket.
     *
     * @param  Collection<string, object>  $erpProducts
     * @param  Collection<string, float>  $available
     * @return int A létrehozott (próbafuttatásnál a létrehozandó) termékek száma.
     */
    private function createNewProducts(Collection $erpProducts, Collection $available, bool $dryRun): int
    {
        $known = IntegraKnownProduct::query()->pluck('code')->mapWithKeys(fn (string $code): array => [$this->codeKey($code) => true]);
        $isFirstRun = $known->isEmpty();
        $existing = Product::query()->whereNotNull('product_code')->pluck('product_code')
            ->mapWithKeys(fn (string $code): array => [$this->codeKey($code) => true]);

        $unseenCodes = $erpProducts->keys()
            ->reject(fn (string $code): bool => $code === '' || $known->has($this->codeKey($code)))
            ->unique($this->codeKey(...))
            ->values();

        $codesToCreate = $isFirstRun ? collect() : $unseenCodes->reject(
            fn (string $code): bool => $existing->has($this->codeKey($code)) || (bool) $erpProducts->get($code)->inactive,
        );

        if ($dryRun) {
            return $codesToCreate->count();
        }

        DB::transaction(function () use ($codesToCreate, $unseenCodes, $erpProducts, $available): void {
            foreach ($codesToCreate as $code) {
                Product::query()->create([
                    ...$this->productAttributes($erpProducts->get($code)),
                    'product_code' => $code,
                    'slug' => $this->uniqueSlug($code),
                    'stock_quantity' => max(0.0, $available->get($code, 0.0)),
                    'is_web_visible' => false,
                ]);
            }

            $unseenCodes->chunk(self::CHUNK_SIZE)->each(fn (Collection $codes): int => IntegraKnownProduct::query()->insertOrIgnore(
                $codes->map(fn (string $code): array => ['code' => $code, 'created_at' => now(), 'updated_at' => now()])->values()->all(),
            ));
        });

        return $codesToCreate->count();
    }

    /**
     * A kód összevetési alakja: a MySQL kollációja a kis- és nagybetűt, valamint
     * az ékezetet nem különbözteti meg („ÜRES” = „ures”), a PHP igen.
     */
    private function codeKey(string $code): string
    {
        return Str::lower(Str::ascii(mb_trim($code)));
    }

    private function uniqueSlug(string $code): string
    {
        $base = Str::slug($code) ?: 'termek';
        $slug = $base;
        $suffix = 0;

        while (Product::query()->where('slug', $slug)->exists()) {
            $suffix++;
            $slug = $base . '-' . $suffix;
        }

        return $slug;
    }

    /**
     * @return array<string, mixed>
     */
    private function productAttributes(object $erpProduct): array
    {
        $netPrice = round((float) $erpProduct->nettoprice, 2);
        $groupCode = mb_trim((string) $erpProduct->product_group_code);
        $typeName = mb_trim((string) $erpProduct->type_name);

        return [
            ...($groupCode === '' ? [] : ['group_code' => $groupCode]),
            ...($typeName === '' ? [] : ['product_variety' => $typeName]),
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
            ->leftJoin('product_types', 'product_types.code', '=', 'products.product_type_code')
            ->get([
                'products.code', 'products.name', 'products.size', 'products.quantity_unit', 'products.nettoprice',
                'products.taxpercent', 'products.is_discount', 'products.discountpercent', 'products.inactive',
                'products.product_group_code', 'product_types.name as type_name',
            ])
            ->keyBy(fn (object $row): string => mb_trim((string) $row->code));
    }

    /**
     * Az Integra termékcsoportjainak neve a vevői kedvezmények és a szűrők
     * csoportjain; az Integrában új csoportkódot is felveszi.
     */
    private function syncProductGroups(): void
    {
        DB::connection('integra7')
            ->table('product_groups')
            ->get(['code', 'name'])
            ->map(fn (object $row): array => ['code' => mb_trim((string) $row->code), 'name' => mb_trim((string) $row->name)])
            ->filter(fn (array $group): bool => $group['code'] !== '')
            ->each(fn (array $group): DiscountGroup => DiscountGroup::query()->updateOrCreate(['code' => $group['code']], ['name' => $group['name']]));
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
