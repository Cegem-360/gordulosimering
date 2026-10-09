<?php

declare(strict_types=1);

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\VatRate;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\DateFormat;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[ObservedBy(OrderObserver::class)]
#[DateFormat('Y-m-d')]
#[Fillable([
    'id',
    'user_id',
    'shipping_method_id',
    'payment_method',
    'payment_method_title',
    'set_paid',
    'billing_name',
    'billing_address_1',
    'billing_address_2',
    'billing_city',
    'billing_state',
    'billing_postcode',
    'billing_country',
    'billing_email',
    'billing_phone',
    'billing_vat_number',
    'billing_company_name',
    'billing_company_office',
    'shipping_name',
    'shipping_address_1',
    'shipping_address_2',
    'shipping_city',
    'shipping_state',
    'shipping_postcode',
    'shipping_country',
    'shipping_tracking_number',
    'parcel_point_id',
    'parcel_point_name',
    'parcel_point_address',
    'order_key',
    'order_status',
    'order_currency',
    'shipping_cost',
])]
final class Order extends Model
{
    use HasFactory;

    /**
     * Whether the next save emails the customer about a status change; the
     * admin can untick "Értesítés küldése a vevőnek" for a correction. Not a
     * column: it lives for one save only. gs@ is told either way.
     */
    public bool $sendsCustomerStatusEmail = true;

    protected $casts = [
        'set_paid' => 'bool',
        'created_at' => 'date',
    ];

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Whether the order goes to a GLS parcel shop or locker.
     */
    public function hasParcelPoint(): bool
    {
        return filled($this->parcel_point_id);
    }

    /**
     * Whether the courier's tracking number is set; an order may have none.
     */
    public function hasTrackingNumber(): bool
    {
        return filled($this->shipping_tracking_number);
    }

    /**
     * The net amount the discounts took off the order.
     */
    public function savings(): float
    {
        return $this->orderItems->sum(fn (OrderItem $item): float => $item->lineSavings());
    }

    /**
     * The 27% VAT on the net product total.
     */
    public function vatAmount(): float
    {
        return $this->orderTotal() * VatRate::Standard->percentage() / 100;
    }

    /**
     * What the customer pays: the products with VAT and the shipping cost,
     * as the checkout totals it.
     */
    public function grossTotal(): float
    {
        return $this->orderTotal() + $this->vatAmount() + $this->shipping_cost;
    }

    public function orderTotal(): int|float
    {
        $total = 0;
        foreach ($this->orderItems as $orderItem) {
            $total += $orderItem->total * $orderItem->quantity;
        }

        return $total;
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'order_status' => OrderStatus::class,
        ];
    }
}
