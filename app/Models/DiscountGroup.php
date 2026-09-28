<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DiscountGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A termékek ERP-s „Csoportkód”-ja (Product::group_code), amelyre a vevő
 * egyedi kedvezményt kaphat. A termékszinkron az új kódokat magától felveszi.
 */
#[Fillable([
    'code',
    'name',
])]
final class DiscountGroup extends Model
{
    /** @use HasFactory<DiscountGroupFactory> */
    use HasFactory;

    public function userDiscounts(): HasMany
    {
        return $this->hasMany(UserDiscount::class);
    }
}
