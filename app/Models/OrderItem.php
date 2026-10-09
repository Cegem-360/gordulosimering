<?php

declare(strict_types=1);

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'product_id',
    'tax_class',
    'subtotal',
    'subtotal_tax',
    'total',
    'total_tax',
    'quantity',
    'regular_price',
    'discount_percentage',
])]
final class OrderItem extends Model
{
    use HasFactory;

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Whether the item was bought at a discount (sale or customer discount);
     * known only for the items ordered since the list price is kept.
     */
    public function hasDiscount(): bool
    {
        return $this->regular_price !== null && (float) $this->discount_percentage > 0;
    }

    /**
     * The net line total at list price, before the discount.
     */
    public function regularLineTotal(): float
    {
        return (float) $this->regular_price * $this->quantity;
    }

    /**
     * The net amount the discount took off the line.
     */
    public function lineSavings(): float
    {
        return $this->hasDiscount() ? $this->regularLineTotal() - (float) $this->subtotal : 0.0;
    }

    /**
     * The gross line total: the net line total and its VAT.
     */
    public function grossLineTotal(): float
    {
        return (float) $this->subtotal + (float) $this->subtotal_tax;
    }

    protected function casts(): array
    {
        return [
            'product_id' => 'int',
            'quantity' => 'int',
            'regular_price' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
        ];
    }
}
