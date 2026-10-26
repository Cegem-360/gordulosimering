<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomerType;
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

#[Fillable(['reference', 'user_id', 'customer_type', 'name', 'email', 'phone', 'company', 'vat_number', 'postcode', 'city', 'address_1', 'address_2', 'country', 'state', 'message', 'status', 'admin_note', 'handled_at'])]
final class QuoteRequest extends Model
{
    /** @use HasFactory<QuoteRequestFactory> */
    use HasFactory;

    public static function generateReference(): string
    {
        return 'AK-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<QuoteRequestItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuoteRequestItem::class);
    }

    /**
     * The billing address on one line, or null for the requests sent before
     * the page asked for it.
     */
    public function address(): ?string
    {
        if (blank($this->postcode) && blank($this->city) && blank($this->address_1)) {
            return null;
        }

        return collect([
            mb_trim("{$this->postcode} {$this->city}"),
            $this->address_1,
            $this->address_2,
            $this->state,
            $this->country,
        ])->filter(fn (?string $part): bool => filled($part))->implode(', ');
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

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function open(Builder $query): void
    {
        $query->whereIn('status', QuoteRequestStatus::open());
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'customer_type' => CustomerType::class,
            'status' => QuoteRequestStatus::class,
            'handled_at' => 'datetime',
        ];
    }
}
