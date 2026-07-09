<?php

declare(strict_types=1);

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id',
    'product_id',
    'cart_id',
    'quantity',
])]
#[Table(name: 'cart_items')]
final class CartItem extends Model
{
    use HasFactory;

    protected $casts = [
        'product_id' => 'int',
        'cart_id' => 'int',
        'quantity' => 'int',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function isSimpleProduct(): bool
    {
        return $this->product->type === ProductType::SIMPLE;
    }
}
