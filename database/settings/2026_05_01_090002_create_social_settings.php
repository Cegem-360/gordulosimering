<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('social.facebook', '');
        $this->migrator->add('social.instagram', '');
        $this->migrator->add('social.twitter', '');
        $this->migrator->add('social.linkedin', '');
        $this->migrator->add('social.youtube', '');
        $this->migrator->add('social.tiktok', '');
        $this->migrator->add('social.pinterest', '');
        $this->migrator->add('social.threads', '');
    }
};
