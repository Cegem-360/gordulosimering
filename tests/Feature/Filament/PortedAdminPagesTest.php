<?php

declare(strict_types=1);

use App\Models\SeoPage;
use App\Models\User;
use App\Settings\GeneralSettings;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    actingAs(User::factory()->create());
});

it('renders the ported settings pages', function (string $path): void {
    get($path)->assertSuccessful();
})->with([
    'admin/manage-general-settings',
    'admin/manage-contact-settings',
    'admin/manage-shop-settings',
    'admin/manage-social-settings',
    'admin/manage-integration-settings',
    'admin/manage-cookie-consent-settings',
]);

it('renders the SeoPage resource index and create pages', function (): void {
    get('admin/seo-pages')->assertSuccessful();
    get('admin/seo-pages/create')->assertSuccessful();
});

it('renders the SeoPage edit page', function (): void {
    $page = SeoPage::query()->create([
        'title' => 'Teszt oldal',
        'content' => 'Tartalom',
    ]);

    get('admin/seo-pages/' . $page->getKey() . '/edit')->assertSuccessful();
});

it('exposes seeded default settings values', function (): void {
    expect(resolve(GeneralSettings::class)->site_name)->toBe('My Website');
});
