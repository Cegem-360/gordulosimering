<?php

declare(strict_types=1);

use App\Enums\NavigationGroup;
use App\Filament\Pages\Settings\ManageContactSettings;
use App\Filament\Pages\Settings\ManageCookieConsentSettings;
use App\Filament\Pages\Settings\ManageGeneralSettings;
use App\Filament\Pages\Settings\ManageIntegrationSettings;
use App\Filament\Pages\Settings\ManageShopSettings;
use App\Filament\Pages\Settings\ManageSocialSettings;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\SeoPages\SeoPageResource;
use App\Filament\Resources\ShippingMethods\ShippingMethodResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    app()->setLocale('hu');
});

it('labels a table column from the field translations', function (): void {
    expect(TextColumn::make('product_code')->getLabel())->toBe('Termékkód');
});

it('labels a relationship table column from the field translations', function (): void {
    expect(TextColumn::make('user.name')->getLabel())->toBe('Vevő');
});

it('keeps an explicitly set column label', function (): void {
    expect(TextColumn::make('product_code')->label('Saját címke')->getLabel())->toBe('Saját címke');
});

it('falls back to the generated label for an untranslated attribute', function (): void {
    expect(TextColumn::make('some_unknown_thing')->getLabel())->toBe('Some unknown thing');
});

it('labels a form field from the field translations', function (): void {
    expect(TextInput::make('billing_city')->getLabel())->toBe('Számlázási város');
});

it('labels an infolist entry from the field translations', function (): void {
    expect(TextEntry::make('created_at')->getLabel())->toBe('Létrehozva');
});

it('labels a table filter from the field translations', function (): void {
    expect(TernaryFilter::make('is_published')->getLabel())->toBe('Közzétéve');
});

it('shows Hungarian column headers on the resource index pages', function (string $path, string $label): void {
    actingAs(User::factory()->create());

    get($path)->assertSuccessful()->assertSee($label);
})->with([
    'products' => ['admin/products', 'Termékkód'],
    'orders' => ['admin/orders', 'Számlázási város'],
    'users' => ['admin/users', 'E-mail cím'],
    'categories' => ['admin/categories', 'Szülő kategória'],
    'shipping methods' => ['admin/shipping-methods', 'Szállítási költség'],
    'seo pages' => ['admin/seo-pages', 'Közzétéve'],
]);

it('shows Hungarian labels for the SEO package fields', function (string $label): void {
    actingAs(User::factory()->create());

    get('admin/seo-pages/create')->assertSuccessful()->assertSee($label);
})->with([
    'SEO cím',
    'Meta leírás',
    'Kulcsszavak',
    'Kanonikus URL',
]);

it('shows Hungarian field labels on the settings pages', function (string $path, string $label): void {
    actingAs(User::factory()->create());

    get($path)->assertSuccessful()->assertSee($label);
})->with([
    ['admin/manage-general-settings', 'Oldal neve'],
    ['admin/manage-general-settings', 'Szlogen'],
    ['admin/manage-general-settings', 'Logó (világos)'],
    ['admin/manage-general-settings', 'Alapértelmezett nyelv'],
    ['admin/manage-general-settings', 'Elérhető nyelvek'],
    ['admin/manage-contact-settings', 'Cégnév'],
    ['admin/manage-contact-settings', 'Adószám'],
    ['admin/manage-contact-settings', 'Cégjegyzékszám'],
    ['admin/manage-contact-settings', 'Utca, házszám'],
    ['admin/manage-contact-settings', 'Város'],
    ['admin/manage-contact-settings', 'Postakód'],
    ['admin/manage-contact-settings', 'Ország'],
    ['admin/manage-contact-settings', 'Google Maps beágyazó kód'],
    ['admin/manage-shop-settings', 'Árazás módja'],
    ['admin/manage-shop-settings', 'Alapértelmezett ÁFA-kulcs'],
    ['admin/manage-shop-settings', 'Pénznem'],
    ['admin/manage-shop-settings', 'Készletkövetés bekapcsolva'],
    ['admin/manage-cookie-consent-settings', 'Sütibanner bekapcsolva'],
    ['admin/manage-cookie-consent-settings', 'Elfogadás gomb'],
    ['admin/manage-cookie-consent-settings', 'Elutasítás gomb'],
    ['admin/manage-cookie-consent-settings', 'Beállítások gomb'],
]);

it('shows the sidebar navigation items in Hungarian', function (string $label): void {
    actingAs(User::factory()->create());

    get('admin/products')->assertSuccessful()->assertSee($label);
})->with([
    'Szállítási módok',
    'Sütikezelés',
    'Közösségi média',
]);

it('translates the navigation groups', function (NavigationGroup $group, string $label): void {
    expect($group->getLabel())->toBe($label);
})->with([
    [NavigationGroup::Webshop, 'Webshop'],
    [NavigationGroup::Content, 'Tartalom'],
    [NavigationGroup::Marketing, 'Marketing'],
    [NavigationGroup::Forms, 'Űrlapok'],
    [NavigationGroup::Media, 'Média'],
    [NavigationGroup::Seo, 'SEO'],
    [NavigationGroup::Settings, 'Beállítások'],
    [NavigationGroup::Users, 'Felhasználók'],
]);

it('groups every resource into a navigation group', function (string $resource, NavigationGroup $group): void {
    expect($resource::getNavigationGroup())->toBe($group);
})->with([
    'products' => [ProductResource::class, NavigationGroup::Webshop],
    'categories' => [CategoryResource::class, NavigationGroup::Webshop],
    'orders' => [OrderResource::class, NavigationGroup::Webshop],
    'shipping methods' => [ShippingMethodResource::class, NavigationGroup::Webshop],
    'users' => [UserResource::class, NavigationGroup::Users],
    'seo pages' => [SeoPageResource::class, NavigationGroup::Content],
]);

it('gives every admin navigation item its own icon', function (): void {
    $icons = collect([
        ProductResource::class,
        CategoryResource::class,
        OrderResource::class,
        ShippingMethodResource::class,
        UserResource::class,
        SeoPageResource::class,
        ManageGeneralSettings::class,
        ManageContactSettings::class,
        ManageShopSettings::class,
        ManageSocialSettings::class,
        ManageIntegrationSettings::class,
        ManageCookieConsentSettings::class,
    ])->mapWithKeys(function (string $class): array {
        $icon = $class::getNavigationIcon();

        return [class_basename($class) => $icon instanceof BackedEnum ? $icon->value : $icon];
    });

    expect($icons->filter())->toHaveCount($icons->count())
        ->and($icons->duplicates())->toBeEmpty();
});
