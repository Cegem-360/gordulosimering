<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\View\Components\CategoryIcon;
use Tests\TestCase;

it('draws every icon the config names as a line icon in the sprite', function (): void {
    $keys = collect(config('category-icons.roots'))
        ->merge(collect(config('category-icons.children'))->flatten())
        ->unique();
    $sprite = file_get_contents(resource_path('images/category-icons.svg'));

    foreach ($keys as $key) {
        preg_match('/<symbol id="' . preg_quote($key, '/') . '" viewBox="0 0 24 24">(.*?)<\/symbol>/s', $sprite, $symbol);

        expect($symbol)->not->toBeEmpty()
            ->and($symbol[1])->toContain('stroke="currentColor" stroke-width="1.75"');
    }

    expect($keys->count())->toBeGreaterThan(20)
        ->and(mb_strlen($sprite))->toBeLessThan(20_000);
});

it('picks the main category icon, a subcategory\'s own icon or its parent\'s, and none for brands', function (): void {
    expect(CategoryIcon::keyFor('CSAPÁGYAK'))->toBe('bearing')
        ->and(CategoryIcon::keyFor('ELEMEK, AKKUMULÁTOROK'))->toBe('battery')
        ->and(CategoryIcon::keyFor('O-GYŰRŰ', 'TÖMÍTÉSEK'))->toBe('o-ring')
        ->and(CategoryIcon::keyFor('ZÁRÓSAPKA', 'TÖMÍTÉSEK'))->toBe('seal')
        ->and(CategoryIcon::keyFor('MUNKAVÉDELMI KESZTYŰ', 'MUNKAVÉDELMI CIPŐ, KESZTYŰ'))->toBe('glove')
        ->and(CategoryIcon::keyFor('SKF', Category::BRAND_ROOT_NAME))->toBeNull()
        ->and(CategoryIcon::keyFor('ISMERETLEN'))->toBeNull();
});

it('shows the icons in the homepage menu and the first fly-out level only', function (): void {
    /** @var TestCase $this */
    $seals = Category::query()->create(['name' => 'TÖMÍTÉSEK', 'slug' => 'tomitesek']);
    $oRing = Category::query()->create(['name' => 'O-GYŰRŰ', 'slug' => 'o-gyuru', 'category_id' => $seals->id]);
    $nbr = Category::query()->create(['name' => 'NBR', 'slug' => 'nbr', 'category_id' => $oRing->id]);
    $brands = Category::query()->create(['name' => Category::BRAND_ROOT_NAME, 'slug' => 'markak']);
    $skf = Category::query()->create(['name' => 'SKF', 'slug' => 'skf', 'category_id' => $brands->id]);
    $nbr->products()->attach(Product::factory()->create());
    $skf->products()->attach(Product::factory()->create());

    $html = (string) $this->blade('<x-category-selector />');
    $sprite = Vite::asset('resources/images/category-icons.svg');

    expect($html)->toContain($sprite . '#seal', $sprite . '#o-ring', $sprite . '#brand-tag')
        ->and(mb_substr_count($html, '<use href="'))->toBe(3);
});
