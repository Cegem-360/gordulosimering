<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Override;

#[Fillable([
    'name',
    'email',
    'password',
    'is_admin',
    'base_discount_percentage',
    'phone',
    'billing_name',
    'billing_company_name',
    'billing_vat_number',
    'billing_company_office',
    'billing_postcode',
    'billing_city',
    'billing_address_1',
    'billing_address_2',
    'billing_country',
    'billing_state',
    'shipping_name',
    'shipping_postcode',
    'shipping_city',
    'shipping_address_1',
    'shipping_address_2',
    'shipping_country',
    'shipping_state',
])]
#[Hidden([
    'password',
    'remember_token',
])]
final class User extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use Notifiable;

    /**
     * Az új vevő alap kedvezménye, egyezik az adatbázis alapértékével, hogy a
     * mentés előtt (pl. a pénztárban regisztrált vevőnél) is ez érvényesüljön.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_admin' => false,
        'base_discount_percentage' => 10,
    ];

    /**
     * Az admin felületre csak az adminok léphetnek be.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(UserDiscount::class);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'base_discount_percentage' => 'decimal:2',
        ];
    }
}
