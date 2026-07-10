<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class IntegrationSettings extends Settings
{
    public string $google_analytics_id;

    public string $google_tag_manager_id;

    public string $facebook_pixel_id;

    public string $hotjar_id;

    public string $linkedin_insight_id;

    public string $chat_plugin_key;

    public static function group(): string
    {
        return 'integrations';
    }
}
