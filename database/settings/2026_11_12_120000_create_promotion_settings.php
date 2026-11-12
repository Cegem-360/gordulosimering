<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * The announcement bar and the homepage banner the client manages in the
 * admin (2026-11-12). Both start switched off, so the site looks as before.
 */
return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('promotion.announcement_enabled', false);
        $this->migrator->add('promotion.announcement_text');
        $this->migrator->add('promotion.announcement_color', '#1b4f9c');
        $this->migrator->add('promotion.announcement_url');
        $this->migrator->add('promotion.banner_enabled', false);
        $this->migrator->add('promotion.banner_desktop_image');
        $this->migrator->add('promotion.banner_mobile_image');
        $this->migrator->add('promotion.banner_url');
        $this->migrator->add('promotion.banner_alt');
    }
};
