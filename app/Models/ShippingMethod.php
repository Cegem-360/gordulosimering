<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VatRate;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable([
    'id',
    'name',
    'title',
    'slug',
    'description',
    'cost',
    'rates',
    'requires_parcel_point',
])]
final class ShippingMethod extends Model
{
    use HasFactory;

    /**
     * The gross cost for a cart of this weight and payment method, or null
     * when the cart is too heavy for this method. Without weight rates the
     * flat cost applies. The rates are net, so the VAT is added here.
     */
    public function costFor(float $weight, string $paymentMethod): ?int
    {
        if (blank($this->rates)) {
            return $this->cost;
        }

        $rate = collect($this->rates)
            ->sortBy('max_weight')
            ->first(fn (array $rate): bool => $weight <= (float) $rate['max_weight']);

        if ($rate === null) {
            return null;
        }

        $netCost = $paymentMethod === 'cod' ? $rate['cash_on_delivery'] : $rate['bank_transfer'];

        return (int) round((float) $netCost * (1 + VatRate::Standard->percentage() / 100));
    }

    /**
     * The heaviest cart this method takes, or null when it has no limit.
     */
    public function maxWeight(): ?float
    {
        if (blank($this->rates)) {
            return null;
        }

        return (float) collect($this->rates)->max('max_weight');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'cost' => 'int',
            'rates' => 'array',
            'requires_parcel_point' => 'bool',
        ];
    }
}
