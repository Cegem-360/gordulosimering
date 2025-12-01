<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $guarded = [];

    public function isInStock(): bool
    {
        return $this->minimum_stock > 0;
    }

    protected function casts(): array
    {
        return [
            'is_service' => 'boolean',
            'weight' => 'decimal:3',
            'is_on_sale' => 'boolean',
            'sale_percentage' => 'decimal:2',
            'list_price' => 'decimal:2',
            'list_discount' => 'decimal:2',
            'purchase_currency_price' => 'decimal:2',
            'currency_multiplier' => 'decimal:4',
            'purchase_price' => 'decimal:2',
            'profit_margin' => 'decimal:2',
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
