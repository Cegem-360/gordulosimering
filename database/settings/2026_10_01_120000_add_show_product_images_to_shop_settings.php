<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Az ügyfél kérésére (2026-10-01) a termékképek ideiglenesen nem látszanak a
 * webshopban, amíg a hozzárendelésüket rendbe teszik: minden termék a
 * helyőrző képet mutatja. Az adminban (Beállítások > Webshop) visszakapcsolható.
 */
return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('shop.show_product_images', false);
    }
};
