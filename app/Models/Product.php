<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    protected function image(): Attribute
    {
        return Attribute::get(fn () => $this->images[0] ?? null);
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
        ];
    }
}
