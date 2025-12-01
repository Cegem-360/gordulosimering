<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Cart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'user_id',
        'session_id',
    ];

    /**
     * Get the products associated with the cart.
     */
    public function products(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isEmpty(): bool
    {
        return $this->products->isEmpty();
    }

    public function total(): int
    {
        $total = 0;
        foreach ($this->items as $cartItem) {
            $total += $cartItem->quantity * $cartItem->product->net_retail_price;
        }

        return $total;
    }
}
