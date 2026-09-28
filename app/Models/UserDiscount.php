<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserDiscountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * Egy vevő kedvezménye egy termék-kedvezménycsoportra.
 */
#[Fillable([
    'user_id',
    'discount_group_id',
    'percentage',
])]
final class UserDiscount extends Model
{
    /** @use HasFactory<UserDiscountFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function discountGroup(): BelongsTo
    {
        return $this->belongsTo(DiscountGroup::class);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
        ];
    }
}
