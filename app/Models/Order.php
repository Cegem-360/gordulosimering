<?php

declare(strict_types=1);

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Order extends Model
{
    use HasFactory;

    protected $dateFormat = 'Y-m-d';

    protected $casts = [
        'set_paid' => 'bool',
        'created_at' => 'date',
    ];

    protected $fillable = [
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
        'order_key',
        'order_status',
        'order_currency',
        'shipping_cost',
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

    public function orderTotal(): int|float
    {
        $total = 0;
        foreach ($this->orderItems as $orderItem) {
            $total += $orderItem->total * $orderItem->quantity;
        }

        return $total;
    }

    protected function casts(): array
    {
        return [
            'order_status' => OrderStatus::class,
        ];
    }
}
