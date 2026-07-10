<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('cookie_consent.enabled', true);
        $this->migrator->add('cookie_consent.title', 'Cookie beállítások');
        $this->migrator->add('cookie_consent.description', 'Weboldalunk sütiket használ a jobb felhasználói élmény érdekében.');
        $this->migrator->add('cookie_consent.accept_button_text', 'Elfogadom');
        $this->migrator->add('cookie_consent.reject_button_text', 'Elutasítom');
        $this->migrator->add('cookie_consent.settings_button_text', 'Beállítások');
        $this->migrator->add('cookie_consent.categories', [
            [
                'name' => 'Szükséges',
                'key' => 'necessary',
                'description' => 'Ezek a sütik elengedhetetlenek a weboldal működéséhez.',
                'required' => true,
            ],
            [
                'name' => 'Analitikai',
                'key' => 'analytics',
                'description' => 'Segítenek megérteni, hogyan használják a látogatók a weboldalt.',
                'required' => false,
            ],
            [
                'name' => 'Marketing',
                'key' => 'marketing',
                'description' => 'Személyre szabott hirdetések megjelenítéséhez használjuk.',
                'required' => false,
            ],
        ]);
    }
};
