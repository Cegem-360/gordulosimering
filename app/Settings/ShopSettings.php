<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class ShopSettings extends Settings
{
    public string $pricing_mode;

    public bool $track_inventory;

    public string $currency;

    public string $default_vat_rate;

    public static function group(): string
    {
        return 'shop';
    }
}
