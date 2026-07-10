<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('shop.pricing_mode', 'gross');
        $this->migrator->add('shop.track_inventory', true);
        $this->migrator->add('shop.currency', 'HUF');
        $this->migrator->add('shop.default_vat_rate', 'standard');
    }
};
