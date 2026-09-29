<?php

declare(strict_types=1);

use App\Settings\CookieConsentSettings;
use App\Settings\IntegrationSettings;
use Tests\TestCase;

function configureGoogleTags(string $googleAnalyticsId, string $googleTagManagerId): void
{
    $settings = resolve(IntegrationSettings::class);
    $settings->google_analytics_id = $googleAnalyticsId;
    $settings->google_tag_manager_id = $googleTagManagerId;
    $settings->save();
}

it('renders the Google Analytics tag when an ID is configured', function (): void {
    /** @var TestCase $this */
    configureGoogleTags('G-TEST12345', '');

    $this->get(route('documents'))->assertOk()
        ->assertSee('https://www.googletagmanager.com/gtag/js?id=G-TEST12345', false)
        ->assertSee('gtag(\'config\', \'G-TEST12345\')', false)
        ->assertDontSee('gtm.js', false);
});

it('renders the Google Tag Manager snippet when an ID is configured', function (): void {
    /** @var TestCase $this */
    configureGoogleTags('', 'GTM-TEST123');

    $this->get(route('documents'))->assertOk()
        ->assertSee('\'GTM-TEST123\'', false)
        ->assertSee('gtm.js', false)
        ->assertDontSee('gtag/js', false);
});

it('renders no Google tag when no ID is configured', function (): void {
    /** @var TestCase $this */
    configureGoogleTags('', '  ');

    $this->get(route('documents'))->assertOk()
        ->assertDontSee('googletagmanager.com', false);
});

it('denies every Google consent type by default while the cookie banner is on', function (): void {
    /** @var TestCase $this */
    configureGoogleTags('G-TEST12345', '');
    configureCookieBanner(true);

    $response = $this->get(route('documents'))->assertOk();

    $content = $response->getContent();

    expect(mb_strpos($content, 'gtag(\'consent\', \'default\''))->toBeLessThan(mb_strpos($content, 'gtag/js?id='))
        ->and($content)->toContain('analytics_storage: \'denied\'')
        ->and($content)->toContain('ad_storage: \'denied\'')
        ->and($content)->toContain('localStorage.getItem(\'cookie_consent\')');
});

it('leaves out the consent defaults when the cookie banner is off', function (): void {
    /** @var TestCase $this */
    configureGoogleTags('G-TEST12345', '');
    configureCookieBanner(false);

    $this->get(route('documents'))->assertOk()
        ->assertSee('gtag/js?id=G-TEST12345', false)
        ->assertDontSee('gtag(\'consent\'', false);
});

it('shows the cookie banner with the texts and categories from the admin', function (): void {
    /** @var TestCase $this */
    configureCookieBanner(true);

    $this->get(route('documents'))->assertOk()
        ->assertSee('Süti-tájékoztató')
        ->assertSee('Mindent elfogadok')
        ->assertSee('Csak a szükségesek')
        ->assertSee('Testreszabás')
        ->assertSee('Statisztika')
        ->assertSee('Süti beállítások');
});

it('hides the cookie banner when it is switched off', function (): void {
    /** @var TestCase $this */
    configureCookieBanner(false);

    $this->get(route('documents'))->assertOk()
        ->assertDontSee('Süti-tájékoztató')
        ->assertDontSee('cookie-consent-title', false);
});

function configureCookieBanner(bool $enabled): void
{
    $settings = resolve(CookieConsentSettings::class);
    $settings->enabled = $enabled;
    $settings->title = 'Süti-tájékoztató';
    $settings->accept_button_text = 'Mindent elfogadok';
    $settings->reject_button_text = 'Csak a szükségesek';
    $settings->settings_button_text = 'Testreszabás';
    $settings->categories = [
        ['name' => 'Szükséges', 'key' => 'necessary', 'description' => 'Működéshez kell.', 'required' => true],
        ['name' => 'Statisztika', 'key' => 'analytics', 'description' => 'Látogatottság mérése.', 'required' => false],
    ];
    $settings->save();
}
