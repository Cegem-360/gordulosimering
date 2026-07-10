<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Override;

final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $guarded = [];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function isInStock(): bool
    {
        return $this->minimum_stock > 0;
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
