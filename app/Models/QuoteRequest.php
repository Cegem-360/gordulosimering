<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuoteRequestStatus;
use Database\Factories\QuoteRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Override;

#[Fillable(['reference', 'user_id', 'name', 'email', 'phone', 'company', 'message', 'status', 'admin_note', 'handled_at'])]
final class QuoteRequest extends Model
{
    /** @use HasFactory<QuoteRequestFactory> */
    use HasFactory;

    public static function generateReference(): string
    {
        return 'AK-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteRequestItem::class);
    }

    /**
     * The net total at the list prices the products had when the request
     * came in; the real figure is what the shop quotes.
     */
    public function indicativeNetTotal(): float
    {
        return round($this->items->sum(
            fn (QuoteRequestItem $item): float => (float) $item->unit_price * $item->quantity,
        ), 2);
    }

    #[Override]
    protected static function booted(): void
    {
        self::creating(function (QuoteRequest $quoteRequest): void {
            $quoteRequest->reference ??= self::generateReference();
        });
    }

    #[Scope]
    protected function open(Builder $query): void
    {
        $query->whereIn('status', QuoteRequestStatus::open());
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'status' => QuoteRequestStatus::class,
            'handled_at' => 'datetime',
        ];
    }
}
