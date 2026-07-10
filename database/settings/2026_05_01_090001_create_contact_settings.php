<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('contact.company_name', '');
        $this->migrator->add('contact.address', '');
        $this->migrator->add('contact.city', '');
        $this->migrator->add('contact.zip_code', '');
        $this->migrator->add('contact.country', 'Magyarország');
        $this->migrator->add('contact.phone', '');
        $this->migrator->add('contact.email', '');
        $this->migrator->add('contact.tax_number', '');
        $this->migrator->add('contact.registration_number', '');
        $this->migrator->add('contact.google_maps_embed', '');
    }
};
