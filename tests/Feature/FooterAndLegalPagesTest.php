<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;

/**
 * The categories the footer's product links point at.
 */
function createFooterCategories(): void
{
    $brands = Category::query()->create(['name' => Category::BRAND_ROOT_NAME, 'slug' => 'forgalmazott-markaink']);
    $chemicals = Category::query()->create(['name' => 'VEGYI ÁRUK', 'slug' => 'vegyi-aruk']);

    foreach ([
        ['SKF', 'forgalmazott-markaink-skf', $brands->id],
        ['LOCTITE', 'forgalmazott-markaink-loctite', $brands->id],
        ['HAJTÁSTECHNIKA', 'hajtastechnika', null],
        ['KÉZISZERSZÁMOK ÉS MŰSZEREK', 'keziszerszamok-es-muszerek', null],
        ['ZSÍR, OLAJ', 'vegyi-aruk-zsir-olaj', $chemicals->id],
        ['TÖMÍTÉSEK', 'tomitesek', null],
    ] as [$name, $slug, $parentId]) {
        Category::query()->create(['name' => $name, 'slug' => $slug, 'category_id' => $parentId])
            ->products()->attach(Product::factory()->create());
    }
}

it('has no dead or placeholder links in the footer', function (): void {
    /** @var TestCase $this */
    createFooterCategories();

    $html = $this->get(route('contact'))->assertOk()->getContent();
    preg_match('/<footer.*<\/footer>/s', $html, $footer);
    preg_match_all('/href="([^"]+)"/', $footer[0], $links);

    expect($links[1])->not->toContain('#')
        ->and(count($links[1]))->toBeGreaterThanOrEqual(16);

    foreach (array_unique($links[1]) as $href) {
        if (str_starts_with($href, 'tel:')) {
            continue;
        }

        $this->get(strtok($href, '#'))->assertOk();
    }
});

it('links the footer products to their real categories and drops a missing one', function (): void {
    /** @var TestCase $this */
    createFooterCategories();
    Category::query()->where('name', 'LOCTITE')->delete();

    $this->get(route('contact'))->assertOk()
        ->assertSeeHtml('href="' . route('categories.show', 'forgalmazott-markaink-skf') . '"')
        ->assertSeeHtml('href="' . route('categories.show', 'vegyi-aruk-zsir-olaj') . '"')
        ->assertSee(['SKF csapágyak', 'Szíjak és láncok', 'Kenőanyagok', 'Tömítések'])
        ->assertDontSee('LOCTITE termékek');
});

it('links the footer services to the matching part of the services page', function (): void {
    /** @var TestCase $this */
    $this->get(route('contact'))->assertOk()
        ->assertSeeHtml('href="' . route('services') . '#ugyelet"')
        ->assertSeeHtml('href="' . route('services') . '#hazhozszallitas"')
        ->assertSeeHtml('href="' . route('services') . '#tovabbi-szolgaltatasok"');

    $this->get(route('services'))->assertOk()
        ->assertSeeHtml('id="ugyelet"')
        ->assertSeeHtml('id="hazhozszallitas"')
        ->assertSeeHtml('id="tovabbi-szolgaltatasok"');
});

it('calls the privacy notice Adatvédelmi nyilatkozat everywhere', function (): void {
    /** @var TestCase $this */
    expect(route('privacy-policy', absolute: false))->toBe('/adatvedelmi-nyilatkozat');

    $this->get(route('privacy-policy'))->assertOk()
        ->assertSeeInOrder(['<h1', 'Adatvédelmi nyilatkozat'], false)
        ->assertDontSee(['Adatkezelési tájékoztató', 'Adatvédelmi politika', 'GDPR</a>'], false);
});

it('sends the old privacy notice address to the new one', function (): void {
    /** @var TestCase $this */
    $this->get('/adatkezelesi-tajekoztato')->assertMovedPermanently()->assertRedirect('/adatvedelmi-nyilatkozat');
});

it('shows the full privacy notice from the old site, without its WordPress-only parts', function (): void {
    /** @var TestCase $this */
    $this->get(route('privacy-policy'))->assertOk()
        ->assertSee([
            '1. Adatkezelő neve',
            'A feliratkozással az érintett hozzájárulását adja',
            '(„Kamerával megfigyelt terület!”)',
            'A felvételek megsemmisítésig, vagy a felhasználásig történő tárolása',
            '15. Jogérvényesítési lehetőségek',
        ])
        ->assertSeeHtml('href="http://support.google.com/chrome/answer/95647?hl=hu"')
        ->assertDontSeeHtml('<ol')
        ->assertDontSee(['Gravatar', 'hozzászól', 'EXIF', 'szerkesztőfelület']);
});

it('keeps the privacy notice current: gs@, the GDPR, this site\'s cookies, the open stores and the authority', function (): void {
    /** @var TestCase $this */
    $this->get(route('privacy-policy'))->assertOk()
        ->assertSee(['gs@gordulo-simmering.hu', 'GDPR 6. cikk (1) bekezdés b) pont', '2000. évi C. törvény 169. §', '8 évig megőrizzük'])
        ->assertSee(['kizárólag a működéséhez szükséges sütiket', 'Munkamenet-süti', 'XSRF-TOKEN', 'remember_web_'])
        ->assertSee(['Nemzeti Adatvédelmi és Információszabadság Hatóság', '1055 Budapest, Falk Miksa utca 9–11.'])
        ->assertDontSee(['info@gordulo-simmering.hu', 'az gs@', 'Nagy Lajos', 'Szilágyi E. fasor', 'statisztikai adatgyűjtés']);
});

it('shows the full terms and conditions from the old site', function (): void {
    /** @var TestCase $this */
    $this->get(route('terms-and-conditions'))->assertOk()
        ->assertSeeInOrder(['I. A szerződés hatálya', 'II. Árak', 'VIII. Reklamáció', 'A. Mennyiségi reklamáció', 'B. Minőségi reklamáció', 'X. Jelen Általános Szerződési Feltételek érvényessége'])
        ->assertSee([
            'A titoktartási kötelezettség megsértése esetén',
            'a fizetési határidőt 60 nappal túllépi',
            'a Vevő által átadott előleg, foglaló, vagy egyéb érték a G-S-nél letétben marad',
            'fogyó anyagnál (pl.: ragasztó, zsír, stb.)',
            'Hatvani Zoltán',
            'Ptk.) 6:155. §-a szerinti mértékben',
            'Fővárosi Törvényszék',
        ])
        ->assertSeeHtml('<p>Budapest, 2026. szeptember 28.</p>')
        ->assertDontSee(['301/A', 'Budapesti Fővárosi Bíróság']);
});

it('lists the open XVII. district store as the company premises, not the closed XIV. one', function (): void {
    /** @var TestCase $this */
    $this->get(route('company-data'))->assertOk()
        ->assertSee(['A cég telephelye', '1173 Budapest, Pesti út 203.'])
        ->assertDontSee(['Nagy Lajos', '1149 Budapest']);
});
