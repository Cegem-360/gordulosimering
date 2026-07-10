<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class GeneralSettings extends Settings
{
    public string $site_name;

    public string $tagline;

    public string $logo_light;

    public string $logo_dark;

    public string $favicon;

    public string $default_locale;

    /** @var array<string> */
    public array $available_locales;

    public static function group(): string
    {
        return 'general';
    }
}
