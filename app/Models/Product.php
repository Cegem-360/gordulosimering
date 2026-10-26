<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VatRate;
use App\Services\CategoryTree;
use App\Services\ProductAttributeExtractor;
use App\Settings\ShopSettings;
use Closure;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Override;

#[Unguarded]
final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * A bejelentkezett vevő kedvezménye a termékre (lásd AppServiceProvider).
     *
     * @var (Closure(Product): float)|null
     */
    private static ?Closure $customerDiscountResolver = null;

    /**
     * @param  (Closure(Product): float)|null  $resolver
     */
    public static function resolveCustomerDiscountUsing(?Closure $resolver): void
    {
        self::$customerDiscountResolver = $resolver;
    }

    /**
     * @return BelongsToMany<Category, $this, Pivot>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Whether the product sits anywhere in the CSAPÁGYAK category tree;
     * bearings show their size beside the product code in listings.
     */
    public function isBearing(): bool
    {
        $bearingIds = resolve(CategoryTree::class)->bearingCategoryIds();

        if ($bearingIds === []) {
            return false;
        }

        if ($this->relationLoaded('categories')) {
            return $this->categories->contains(fn (Category $category): bool => in_array($category->id, $bearingIds, true));
        }

        return $this->categories()->whereIn('product_categories.id', $bearingIds)->exists();
    }

    /**
     * The stock on hand for the product page, e.g. "23 db" or "12,5 m";
     * null when nothing is in stock.
     */
    public function stockQuantityLabel(): ?string
    {
        if (! $this->isInStock()) {
            return null;
        }

        $unit = mb_trim((string) $this->quantity_unit) ?: 'db';

        return Number::format((float) $this->stock_quantity, maxPrecision: 2, locale: 'hu') . ' ' . $unit;
    }

    /**
     * The uploaded documents for the product page, with the name the file
     * was uploaded under. Uploads from before the names were kept are
     * stored under random names, so they are numbered instead.
     *
     * @return list<array{url: string, name: string}>
     */
    public function documentLinks(): array
    {
        $names = $this->document_names ?? [];

        return collect($this->documents ?? [])
            ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
            ->values()
            ->map(fn (string $path, int $index): array => [
                'url' => Storage::disk('public')->url($path),
                'name' => $names[$path] ?? 'Dokumentum ' . ($index + 1),
            ])
            ->values()
            ->all();
    }

    /**
     * The rough stock level the product cards show instead of the count.
     * Exactly 10 still counts as "fewer than 10" and exactly 50 as "more
     * than 10", as the client asked; null when nothing is in stock.
     */
    public function stockLevelLabel(): ?string
    {
        return match (true) {
            ! $this->isInStock() => null,
            $this->stock_quantity > 50 => '50-nél több van raktáron',
            $this->stock_quantity > 10 => '10-nél több van raktáron',
            default => '10-nél kevesebb van raktáron',
        };
    }

    /**
     * The step orderable quantities go up in: the order unit ("Rendelési
     * egység", e.g. 10 for batteries sold in tens), or 1 when it is not set.
     */
    public function orderQuantityStep(): int
    {
        return max(1, (int) $this->order_unit);
    }

    /**
     * The smallest orderable quantity: one order unit, so 1 unless the shop
     * set a "Rendelési egység" by hand (e.g. batteries sold in tens). The
     * ERP's "Min. rendelhető" is the shop's own purchasing minimum and does
     * not apply to customers (client, 2026-10-13).
     */
    public function minimumOrderQuantity(): int
    {
        return $this->orderQuantityStep();
    }

    /**
     * The nearest orderable quantity at or above the requested one: never
     * below the minimum, and always a whole number of order units.
     */
    public function orderableQuantity(int $quantity): int
    {
        return max($this->minimumOrderQuantity(), $this->roundUpToOrderUnit($quantity));
    }

    /**
     * Whether the product page shows this field ("Egyéb adatok és kódok" in
     * the admin). Every field shows unless the admin switched it off for
     * this product.
     */
    public function showsField(string $field): bool
    {
        return ($this->field_visibility[$field] ?? true) !== false;
    }

    /**
     * The field's value for the product page, or null when it is hidden.
     */
    public function visibleField(string $field): mixed
    {
        return $this->showsField($field) ? $this->{$field} : null;
    }

    public function isOnSale(): bool
    {
        return (bool) $this->is_on_sale;
    }

    /**
     * Kap-e a vevő kedvezményt a termékre: akciót vagy vevőkedvezményt.
     */
    public function hasDiscount(): bool
    {
        return $this->discount_percentage > 0;
    }

    /**
     * Recalculates the filter attributes (ProductAttributeExtractor) on save
     * when the name or size changed; a caller saving a partially selected
     * product must load both `name` and `size`.
     */
    #[Override]
    protected static function booted(): void
    {
        self::saving(function (Product $product): void {
            if (! $product->exists || $product->isDirty(['name', 'size'])) {
                $product->fill(resolve(ProductAttributeExtractor::class)->extract($product->name, $product->size));
            }
        });
    }

    /**
     * A webshop felületén megjelenő termékek. Az ERP-export
     * "WEBÁRUHÁZBAN SZEREPELJEN" oszlopa dönt róla. A kosár és az admin
     * szándékosan nem használja, hogy a már kosárba tett vagy megrendelt
     * termék akkor is elérhető maradjon, ha időközben lekerült a webről.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function webVisible(Builder $query): void
    {
        $query->where('is_web_visible', true);
    }

    /**
     * Az adminban kiemeltnek jelölt termékek (főoldal, "Kiemelt termékeink").
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * Keresés termékkódra, névre és méretre, részszóra is. A tizedesvessző és
     * a tizedespont egyenértékű: a "25,4x50,8" és a "25.4x50.8" ugyanazt adja,
     * mert a vevők és az ERP-export sem egységesen írják a méreteket.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function matchingSearch(Builder $query, string $term): void
    {
        $pattern = '%' . $this->normalizeDecimalSeparator($term) . '%';

        $query->where(function (Builder $query) use ($pattern): void {
            foreach (['product_code', 'name', 'size'] as $column) {
                $query->orWhereRaw("REPLACE({$column}, ',', '.') LIKE ?", [$pattern]);
            }
        });
    }

    /**
     * A Márkáink oldal egy márkájának (config/brands.php) termékei: a márka
     * mező, a név egy egész szava vagy a termékkód egy része szerint.
     *
     * @param  Builder<self>  $query
     * @param  array{filter?: string, name_word?: string, code_part?: string}  $brand
     */
    #[Scope]
    protected function ofCatalogBrand(Builder $query, array $brand): void
    {
        match (true) {
            isset($brand['filter']) => $query->where('brand', $brand['filter']),
            isset($brand['name_word']) => $query->where(function (Builder $query) use ($brand): void {
                $word = $this->escapeLike($brand['name_word']);

                foreach (['', '% ', '%/', '%('] as $before) {
                    foreach (['', ' %', '/%', ',%', ')%'] as $after) {
                        $query->orWhereRaw('name LIKE ? ESCAPE \'!\'', [$before . $word . $after]);
                    }
                }
            }),
            isset($brand['code_part']) => $query->whereRaw('product_code LIKE ? ESCAPE \'!\'', ['%' . $this->escapeLike($brand['code_part']) . '%']),
            default => $query->whereRaw('1 = 0'),
        };
    }

    /**
     * Cikkszám szerinti sorrend, a számmal kezdődők előbb (a márkák
     * terméklistája, az ügyfél kérése, 2026-10-26).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function orderByProductCode(Builder $query): void
    {
        $query->orderBy('product_code');
    }

    /**
     * A készleten lévő termékek kerülnek előre, utánuk a rendelhetők.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function inStockFirst(Builder $query): void
    {
        $query->orderByRaw('CASE WHEN stock_quantity > 0 THEN 0 ELSE 1 END');
    }

    /**
     * A termékkóddal kezdődő találatok kerülnek előre, azon belül az SKF
     * termékek (az ügyfél kérése, 2026-10-13).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function orderBySearchRelevance(Builder $query, string $term): void
    {
        $query->orderByRaw(
            'CASE WHEN REPLACE(product_code, \',\', \'.\') LIKE ? THEN 0 ELSE 1 END',
            [$this->normalizeDecimalSeparator($term) . '%'],
        )->orderByRaw('CASE WHEN brand = \'SKF\' THEN 0 ELSE 1 END');
    }

    /**
     * The primary image path: the dedicated featured image, or the first
     * gallery image as a fallback.
     */
    protected function image(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->featured_image ?? ($this->images[0] ?? null));
    }

    /**
     * Public URL of the primary image, resolving disk-relative upload paths.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => resolve(ShopSettings::class)->show_product_images ? $this->resolveImageUrl($this->image) : null);
    }

    /**
     * Az érvényes kedvezmény százalékban. Akciós terméknél az ERP „Akció %”
     * értéke, vagy 10%, ha az 0 vagy üres; nem akciósnál 0. 0 és 100 közé szorítva.
     */
    protected function effectiveSalePercentage(): Attribute
    {
        return Attribute::get(function (): float {
            if (! $this->isOnSale()) {
                return 0.0;
            }

            $percentage = (float) $this->sale_percentage;

            return $percentage > 0 ? min($percentage, 100.0) : 10.0;
        });
    }

    /**
     * Akciós nettó egységár egész forintra kerekítve; nem akciós terméknél null.
     */
    protected function salePrice(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->isOnSale()
            ? (int) round((float) $this->net_selling_price * (100 - $this->effective_sale_percentage) / 100)
            : null);
    }

    /**
     * A vevőnek járó kedvezmény százalékban: az akció és a vevő kedvezménye
     * (alap vagy a termék csoportkódjára adott) közül a nagyobb. Nem
     * adódnak össze.
     */
    protected function discountPercentage(): Attribute
    {
        return Attribute::get(fn (): float => max(
            $this->effective_sale_percentage,
            self::$customerDiscountResolver instanceof Closure ? (self::$customerDiscountResolver)($this) : 0.0,
        ));
    }

    /**
     * Kedvezményes nettó egységár egész forintra kerekítve; kedvezmény nélkül null.
     */
    protected function discountedPrice(): Attribute
    {
        return Attribute::get(fn (): ?int => $this->hasDiscount()
            ? (int) round((float) $this->net_selling_price * (100 - $this->discount_percentage) / 100)
            : null);
    }

    /**
     * A vevő által fizetendő nettó egységár: kedvezménnyel a kedvezményes ár.
     */
    protected function unitPrice(): Attribute
    {
        return Attribute::get(fn (): float => (float) ($this->discounted_price ?? $this->net_selling_price));
    }

    /**
     * A vevő által fizetendő bruttó egységár: a (kedvezményes) nettó
     * egységár 27% ÁFÁ-val. Számolt érték, mert a vevő kedvezménye a
     * bejelentkezett vevőtől függ, így adatbázisban nem tárolható.
     */
    protected function grossUnitPrice(): Attribute
    {
        return Attribute::get(fn (): float => $this->unit_price * (1 + VatRate::Standard->percentage() / 100));
    }

    /**
     * All gallery image URLs, with the featured image first.
     *
     * @return Attribute<array<int, string>, never>
     */
    protected function galleryUrls(): Attribute
    {
        return Attribute::get(function (): array {
            if (! resolve(ShopSettings::class)->show_product_images) {
                return [];
            }

            $paths = array_values(array_filter([
                $this->featured_image,
                ...($this->images ?? []),
            ]));

            return array_values(array_filter(array_map($this->resolveImageUrl(...), $paths)));
        });
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'is_service' => 'boolean',
            'is_web_visible' => 'boolean',
            'is_inactive' => 'boolean',
            'weight' => 'decimal:3',
            'inner_diameter' => 'float',
            'outer_diameter' => 'float',
            'width' => 'float',
            'is_on_sale' => 'boolean',
            'is_featured' => 'boolean',
            'sale_percentage' => 'decimal:2',
            'net_selling_price' => 'decimal:2',
            'gross_selling_price' => 'decimal:2',
            'minimum_stock' => 'integer',
            'stock_quantity' => 'float',
            'maximum_stock' => 'integer',
            'buffer_stock' => 'integer',
            'order_unit' => 'integer',
            'min_order_quantity' => 'integer',
            'trade_quantity' => 'integer',
            'pallet_quantity' => 'integer',
            'custom_fields' => 'array',
            'field_visibility' => 'array',
            'images' => 'json',
            'documents' => 'json',
            'document_names' => 'array',
        ];
    }

    private function normalizeDecimalSeparator(string $term): string
    {
        return str_replace(',', '.', mb_trim($term));
    }

    /**
     * Escapes the LIKE wildcards for an ESCAPE '!' clause.
     */
    private function escapeLike(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }

    /**
     * Whether the storefront shows product photos (admin: Beállítások >
     * Webshop). Turned off on 2026-10-01 while the client sorts out which
     * photo belongs to which product; the views then fall back to the
     * placeholder. The admin reads the raw columns, so it is unaffected.
     */
    private function showsImages(): bool
    {
        return resolve(ShopSettings::class)->show_product_images;
    }

    private function roundUpToOrderUnit(int $quantity): int
    {
        $step = $this->orderQuantityStep();

        return (int) (ceil(max(1, $quantity) / $step) * $step);
    }

    private function resolveImageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
