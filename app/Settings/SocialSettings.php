<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class SocialSettings extends Settings
{
    public string $facebook;

    public string $instagram;

    public string $twitter;

    public string $linkedin;

    public string $youtube;

    public string $tiktok;

    public string $pinterest;

    public string $threads;

    public static function group(): string
    {
        return 'social';
    }
}
