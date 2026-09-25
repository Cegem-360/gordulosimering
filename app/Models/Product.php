<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Override;

#[Unguarded]
final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function isInStock(): bool
    {
        return $this->minimum_stock > 0;
    }

    public function isOnSale(): bool
    {
        return (bool) $this->is_on_sale;
    }

    /**
     * A webshop felületén megjelenő termékek. Az ERP-export
     * "WEBÁRUHÁZBAN SZEREPELJEN" oszlopa dönt róla. A kosár és az admin
     * szándékosan nem használja, hogy a már kosárba tett vagy megrendelt
     * termék akkor is elérhető maradjon, ha időközben lekerült a webről.
     *
     * @param  Builder<Product>  $query
     */
    #[Scope]
    protected function webVisible(Builder $query): void
    {
        $query->where('is_web_visible', true);
    }

    /**
     * Keresés termékkódra, névre és méretre, részszóra is. A tizedesvessző és
     * a tizedespont egyenértékű: a "25,4x50,8" és a "25.4x50.8" ugyanazt adja,
     * mert a vevők és az ERP-export sem egységesen írják a méreteket.
     *
     * @param  Builder<Product>  $query
     */
    #[Scope]
    protected function matchingSearch(Builder $query, string $term): void
    {
        $pattern = '%' . self::normalizeDecimalSeparator($term) . '%';

        $query->where(function (Builder $query) use ($pattern): void {
            foreach (['product_code', 'name', 'size'] as $column) {
                $query->orWhereRaw("REPLACE({$column}, ',', '.') LIKE ?", [$pattern]);
            }
        });
    }

    /**
     * A termékkóddal kezdődő találatok kerülnek előre.
     *
     * @param  Builder<Product>  $query
     */
    #[Scope]
    protected function orderBySearchRelevance(Builder $query, string $term): void
    {
        $query->orderByRaw(
            'CASE WHEN REPLACE(product_code, \',\', \'.\') LIKE ? THEN 0 ELSE 1 END',
            [self::normalizeDecimalSeparator($term) . '%'],
        );
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
        return Attribute::get(fn (): ?string => $this->resolveImageUrl($this->image));
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
     * A vevő által fizetendő nettó egységár: akciós terméknél az akciós ár.
     */
    protected function unitPrice(): Attribute
    {
        return Attribute::get(fn (): float => (float) ($this->sale_price ?? $this->net_selling_price));
    }

    /**
     * All gallery image URLs, with the featured image first.
     *
     * @return Attribute<array<int, string>, never>
     */
    protected function galleryUrls(): Attribute
    {
        return Attribute::get(function (): array {
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
            'is_on_sale' => 'boolean',
            'sale_percentage' => 'decimal:2',
            'net_selling_price' => 'decimal:2',
            'gross_selling_price' => 'decimal:2',
            'minimum_stock' => 'integer',
            'maximum_stock' => 'integer',
            'buffer_stock' => 'integer',
            'order_unit' => 'integer',
            'min_order_quantity' => 'integer',
            'trade_quantity' => 'integer',
            'pallet_quantity' => 'integer',
            'custom_fields' => 'array',
            'images' => 'json',
            'documents' => 'json',
        ];
    }

    private static function normalizeDecimalSeparator(string $term): string
    {
        return str_replace(',', '.', mb_trim($term));
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
