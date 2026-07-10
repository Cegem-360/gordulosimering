<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class CookieConsentSettings extends Settings
{
    public bool $enabled;

    public string $title;

    public string $description;

    public string $accept_button_text;

    public string $reject_button_text;

    public string $settings_button_text;

    public array $categories;

    public static function group(): string
    {
        return 'cookie_consent';
    }
}
