<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('integrations.google_analytics_id', '');
        $this->migrator->add('integrations.google_tag_manager_id', '');
        $this->migrator->add('integrations.facebook_pixel_id', '');
        $this->migrator->add('integrations.hotjar_id', '');
        $this->migrator->add('integrations.linkedin_insight_id', '');
        $this->migrator->add('integrations.chat_plugin_key', '');
    }
};
