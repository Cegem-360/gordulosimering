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

    /**
     * Whether the storefront shows product photos; off shows the placeholder
     * on every product. The admin always shows the real images.
     */
    public bool $show_product_images;

    public static function group(): string
    {
        return 'shop';
    }
}
