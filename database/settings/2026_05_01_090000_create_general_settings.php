<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'My Website');
        $this->migrator->add('general.tagline', '');
        $this->migrator->add('general.logo_light', '');
        $this->migrator->add('general.logo_dark', '');
        $this->migrator->add('general.favicon', '');
        $this->migrator->add('general.default_locale', 'hu');
        $this->migrator->add('general.available_locales', ['hu', 'en']);
    }
};
