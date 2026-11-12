<?php

declare(strict_types=1);

use App\Filament\Pages\Settings\ManagePromotionSettings;
use App\Models\User;
use App\Settings\PromotionSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * @param  array<string, mixed>  $values
 */
function promotion(array $values): void
{
    $settings = resolve(PromotionSettings::class);

    foreach ($values as $key => $value) {
        $settings->{$key} = $value;
    }

    $settings->save();
}

it('starts with both the bar and the banner switched off', function (): void {
    get(route('index'))->assertOk()
        ->assertDontSeeHtml('data-announcement-bar')
        ->assertDontSeeHtml('data-home-banner');
});

it('shows the announcement bar above the header on every page', function (): void {
    promotion(['announcement_enabled' => true, 'announcement_text' => 'December 24-én zárva tartunk.', 'announcement_color' => '#dc2626']);

    foreach ([route('index'), route('contact'), route('login')] as $url) {
        get($url)->assertOk()
            ->assertSeeInOrder(['data-announcement-bar', 'December 24-én zárva tartunk.', 'Main Navigation'], false)
            ->assertSeeHtml('style="background-color: #dc2626; color: #ffffff;"');
    }
});

it('links the whole bar only when a link is given', function (): void {
    promotion(['announcement_enabled' => true, 'announcement_text' => 'Téli akció', 'announcement_url' => '/akciok']);

    get(route('contact'))->assertSeeHtml('<a  href="/akciok"');

    promotion(['announcement_url' => null]);

    get(route('contact'))->assertSee('Téli akció')->assertDontSeeHtml('href="/akciok"');
});

it('hides the bar when it is switched off or has no text', function (array $values): void {
    promotion($values);

    get(route('contact'))->assertDontSeeHtml('data-announcement-bar');
})->with([
    'switched off' => [['announcement_enabled' => false, 'announcement_text' => 'Zárva']],
    'no text' => [['announcement_enabled' => true, 'announcement_text' => null]],
]);

it('picks the readable text colour for the background', function (string $background, string $text): void {
    promotion(['announcement_color' => $background]);

    expect(resolve(PromotionSettings::class)->announcementTextColor())->toBe($text);
})->with([
    'red' => ['#dc2626', '#ffffff'],
    'green' => ['#15803d', '#ffffff'],
    'black' => ['#000000', '#ffffff'],
    'yellow' => ['#facc15', '#000000'],
    'white' => ['#ffffff', '#000000'],
    'short form' => ['#ff0', '#000000'],
    'broken value' => ['piros', '#ffffff'],
]);

it('shows the banner under the header on the homepage only, with its own phone image', function (): void {
    promotion([
        'banner_enabled' => true,
        'banner_desktop_image' => 'banners/desktop.jpg',
        'banner_mobile_image' => 'banners/mobile.jpg',
        'banner_url' => '/akciok',
        'banner_alt' => 'Téli SKF akció',
    ]);

    get(route('index'))->assertOk()
        ->assertSeeInOrder(['Main Navigation', 'data-home-banner', 'Keresés a termékek között'], false)
        ->assertSeeHtml('<a href="/akciok" class="block">')
        ->assertSeeHtml('media="(max-width: 767px)" srcset="' . Storage::disk('public')->url('banners/mobile.jpg') . '"')
        ->assertSeeHtml('src="' . Storage::disk('public')->url('banners/desktop.jpg') . '" alt="Téli SKF akció"');

    get(route('contact'))->assertDontSeeHtml('data-home-banner');
});

it('uses the desktop image on phones when no phone image was uploaded, and needs no link', function (): void {
    promotion(['banner_enabled' => true, 'banner_desktop_image' => 'banners/desktop.jpg', 'banner_mobile_image' => null, 'banner_url' => null]);

    get(route('index'))
        ->assertSeeHtml('srcset="' . Storage::disk('public')->url('banners/desktop.jpg') . '"')
        ->assertDontSeeHtml('<a href="" class="block">');
});

it('collapses the banner when it is switched off or has no image', function (array $values): void {
    promotion($values);

    get(route('index'))->assertOk()->assertDontSeeHtml('data-home-banner');
})->with([
    'switched off' => [['banner_enabled' => false, 'banner_desktop_image' => 'banners/desktop.jpg']],
    'no image' => [['banner_enabled' => true, 'banner_desktop_image' => null]],
]);

it('lets the admin set up the bar and the banner', function (): void {
    Storage::fake('public');
    actingAs(User::factory()->create(['is_admin' => true]));

    Livewire::test(ManagePromotionSettings::class)
        ->assertFormSet(['announcement_enabled' => false, 'banner_enabled' => false])
        ->fillForm([
            'announcement_enabled' => true,
            'announcement_text' => ' Rendkívüli nyitvatartás ',
            'announcement_color' => '#facc15',
            'announcement_url' => 'https://example.com/nyitvatartas',
            'banner_enabled' => true,
            'banner_desktop_image' => UploadedFile::fake()->image('desktop.jpg', 1920, 500),
            'banner_url' => '/akciok',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = resolve(PromotionSettings::class)->refresh();

    expect($settings->announcement_text)->toBe('Rendkívüli nyitvatartás')
        ->and($settings->announcement_color)->toBe('#facc15')
        ->and($settings->showsAnnouncement())->toBeTrue()
        ->and($settings->showsBanner())->toBeTrue()
        ->and($settings->banner_desktop_image)->toStartWith('banners/');

    Storage::disk('public')->assertExists($settings->banner_desktop_image);
});

it('requires the text and the desktop image when switched on, and a proper link', function (): void {
    actingAs(User::factory()->create(['is_admin' => true]));

    Livewire::test(ManagePromotionSettings::class)
        ->fillForm([
            'announcement_enabled' => true,
            'announcement_text' => '',
            'announcement_url' => 'javascript:alert(1)',
            'banner_enabled' => true,
            'banner_url' => 'akciok',
        ])
        ->call('save')
        ->assertHasFormErrors(['announcement_text', 'announcement_url', 'banner_desktop_image', 'banner_url']);
});
