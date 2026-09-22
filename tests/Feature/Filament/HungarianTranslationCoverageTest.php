<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Enums\VatRate;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\SeoPages\SeoPageResource;
use App\Filament\Resources\ShippingMethods\ShippingMethodResource;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * Filament ships its own `hu` translations, but they lag behind the `en` files of
 * the version we run, so untranslated keys silently render in English. These keys
 * are the same in both languages and are expected to match.
 */
const INTENTIONALLY_UNTRANSLATED = [
    'filament-panels::layout.direction',
    'filament-panels::widgets/filament-info-widget.actions.open_github.label',
    'filament-forms::components.file_upload.editor.fields.height.unit',
    'filament-forms::components.file_upload.editor.fields.width.unit',
    'filament-forms::components.file_upload.editor.fields.x_position.label',
    'filament-forms::components.file_upload.editor.fields.x_position.unit',
    'filament-forms::components.file_upload.editor.fields.y_position.label',
    'filament-forms::components.file_upload.editor.fields.y_position.unit',
    'filament-forms::components.rich_editor.actions.link.modal.form.url.label',
];

/**
 * @return array<string, string> untranslated key => the English string it falls back to
 */
function untranslatedFilamentStrings(): array
{
    $packages = [
        'filament' => 'vendor/filament/support/resources/lang',
        'filament-panels' => 'vendor/filament/filament/resources/lang',
        'filament-tables' => 'vendor/filament/tables/resources/lang',
        'filament-forms' => 'vendor/filament/forms/resources/lang',
        'filament-schemas' => 'vendor/filament/schemas/resources/lang',
        'filament-infolists' => 'vendor/filament/infolists/resources/lang',
        'filament-actions' => 'vendor/filament/actions/resources/lang',
        'filament-notifications' => 'vendor/filament/notifications/resources/lang',
        'filament-widgets' => 'vendor/filament/widgets/resources/lang',
    ];

    $flatten = function (array $lines, string $prefix = '') use (&$flatten): array {
        $flat = [];

        foreach ($lines as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            $flat += is_array($value) ? $flatten($value, $path) : [$path => $value];
        }

        return $flat;
    };

    $untranslated = [];

    foreach ($packages as $namespace => $packageLangPath) {
        $englishDir = base_path($packageLangPath . '/en');

        if (! is_dir($englishDir)) {
            continue;
        }

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($englishDir)) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $group = mb_trim(str_replace([$englishDir, '.php'], '', $file->getPathname()), '/');

            $english = $flatten(require $file->getPathname());

            $fromPackage = is_file($path = base_path($packageLangPath . '/hu/' . $group . '.php'))
                ? $flatten(require $path)
                : [];

            $fromOverride = is_file($path = base_path('lang/vendor/' . $namespace . '/hu/' . $group . '.php'))
                ? $flatten(require $path)
                : [];

            $hungarian = array_replace($fromPackage, $fromOverride);

            foreach ($english as $key => $englishValue) {
                if (! is_string($englishValue) || mb_trim($englishValue) === '') {
                    continue;
                }

                $fullKey = $namespace . '::' . $group . '.' . $key;

                if (in_array($fullKey, INTENTIONALLY_UNTRANSLATED, true)) {
                    continue;
                }

                if (! array_key_exists($key, $hungarian) || $hungarian[$key] === $englishValue) {
                    $untranslated[$fullKey] = $englishValue;
                }
            }
        }
    }

    return $untranslated;
}

it('has a Hungarian translation for every Filament interface string', function (): void {
    expect(untranslatedFilamentStrings())->toBe([]);
});

it('labels the order statuses in Hungarian', function (OrderStatus $status, string $label): void {
    expect($status->getLabel())->toBe($label);
})->with([
    [OrderStatus::PENDING, 'Feldolgozásra vár'],
    [OrderStatus::PROCESSING, 'Feldolgozás alatt'],
    [OrderStatus::ONHOLD, 'Várakoztatva'],
    [OrderStatus::COMPLETED, 'Teljesítve'],
    [OrderStatus::CANCELLED, 'Törölve'],
    [OrderStatus::REFUNDED, 'Visszatérítve'],
    [OrderStatus::FAILED, 'Sikertelen'],
    [OrderStatus::TRASH, 'Kukában'],
]);

it('labels the vat exempt rate in Hungarian', function (): void {
    expect(VatRate::Exempt->label())->toBe('ÁFA-mentes');
});

it('labels the seo page model in Hungarian', function (): void {
    expect(SeoPageResource::getModelLabel())->toBe('SEO oldal')
        ->and(SeoPageResource::getPluralModelLabel())->toBe('SEO oldalak');
});

it('formats the admin money columns in forints', function (): void {
    actingAs(User::factory()->create());
    Product::factory()->create(['net_selling_price' => 1234, 'gross_selling_price' => 1567]);

    get('admin/products')->assertSuccessful()->assertDontSee('USD');
});

it('keeps the resource page titles in Hungarian sentence case', function (string $resource, string $title): void {
    expect($resource::getTitleCasePluralModelLabel())->toBe($title);
})->with([
    'seo pages' => [SeoPageResource::class, 'SEO oldalak'],
    'shipping methods' => [ShippingMethodResource::class, 'Szállítási módok'],
]);

it('hides the cookie category repeater label rather than falling back to English', function (): void {
    actingAs(User::factory()->create());

    get('admin/manage-cookie-consent-settings')->assertSuccessful()->assertDontSee('Categories');
});

it('reports a completed product import in Hungarian', function (): void {
    $import = (new Import())->forceFill(['total_rows' => 10, 'successful_rows' => 10]);

    expect(ProductImporter::getCompletedNotificationBody($import))
        ->toBe('A termékimport befejeződött, 10 sor importálva.');
});

it('reports the failed rows of a product import in Hungarian', function (): void {
    $import = (new Import())->forceFill(['total_rows' => 10, 'successful_rows' => 7]);

    expect(ProductImporter::getCompletedNotificationBody($import))
        ->toBe('A termékimport befejeződött, 7 sor importálva. 3 sor importálása nem sikerült.');
});

it('formats numbers and money with the Hungarian locale', function (): void {
    expect(Number::format(50923))->toBe("50\u{A0}923")
        ->and(Number::currency(1234))->toBe("1\u{A0}234,00\u{A0}Ft");
});
