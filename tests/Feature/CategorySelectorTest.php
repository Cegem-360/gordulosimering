<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use Tests\TestCase;

it('lists the root categories and nested subcategories on the homepage', function (): void {
    /** @var TestCase $this */
    $root = Category::query()->create(['name' => 'Csapágyak', 'slug' => 'csapagyak']);
    $seals = Category::query()->create(['name' => 'Tömítések', 'slug' => 'tomitesek']);
    $child = Category::query()->create(['name' => 'Golyóscsapágyak', 'slug' => 'golyoscsapagyak', 'category_id' => $root->id]);
    $grandchild = Category::query()->create(['name' => 'Mélyhornyú', 'slug' => 'melyhornyu', 'category_id' => $child->id]);
    $greatGrandchild = Category::query()->create(['name' => 'Egysoros', 'slug' => 'egysoros', 'category_id' => $grandchild->id]);
    $greatGrandchild->products()->attach(Product::factory()->create());
    $seals->products()->attach(Product::factory()->create());

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Csapágyak')
        ->assertSee('Tömítések')
        ->assertSeeHtml(route('categories.show', $root))
        ->assertSee($child->name)
        ->assertSeeHtml(route('categories.show', $child))
        ->assertSee($grandchild->name)
        ->assertSee($greatGrandchild->name); // 4th level renders through the recursive fly-out
});

it('shows a fallback when there are no categories', function (): void {
    /** @var TestCase $this */
    $response = $this->get('/');

    $response->assertOk()->assertSee('Nincsenek kategóriák.');
});

it('lists the brand category last, after the alphabetical product categories', function (): void {
    /** @var TestCase $this */
    $categories = [
        Category::query()->create(['name' => Category::BRAND_ROOT_NAME, 'slug' => 'forgalmazott-markaink']),
        Category::query()->create(['name' => 'ZSÍRZÁSTECHNIKA', 'slug' => 'zsirzastechnika']),
        Category::query()->create(['name' => 'BILINCSEK', 'slug' => 'bilincsek']),
    ];
    foreach ($categories as $category) {
        $category->products()->attach(Product::factory()->create());
    }

    expect(Category::query()->menuRoots()->pluck('name')->all())
        ->toBe(['BILINCSEK', 'ZSÍRZÁSTECHNIKA', Category::BRAND_ROOT_NAME]);

    $this->get('/')->assertOk()->assertSeeInOrder(['BILINCSEK', 'ZSÍRZÁSTECHNIKA', Category::BRAND_ROOT_NAME]);
});

it('opens a fly-out submenu only from its own parent item', function (): void {
    /** @var TestCase $this */
    $root = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    Category::query()->create(['name' => 'GOLYÓS CSAPÁGY', 'slug' => 'golyos-csapagy', 'category_id' => $root->id])
        ->products()->attach(Product::factory()->create());

    $this->get('/')->assertOk()
        ->assertSeeHtml('[&:hover>ul]:visible')
        ->assertDontSeeHtml('group-hover/item:visible');
});

it('shows the real categories in the top menu and no brands link', function (): void {
    /** @var TestCase $this */
    $category = Category::query()->create(['name' => 'TÖMÍTÉSEK', 'slug' => 'tomitesek']);
    $category->products()->attach(Product::factory()->create());

    $this->get(route('contact'))->assertOk()
        ->assertSeeHtml(route('categories.show', $category))
        ->assertDontSee('Mélyhornyú golyóscsapágyak')
        ->assertDontSee('Márkák');
});

it('keeps categories without web-visible products out of the sidebar and the top menu', function (): void {
    /** @var TestCase $this */
    $stocked = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $stocked->products()->attach(Product::factory()->create());
    Category::query()->create(['name' => 'MUNKAVÉDELMI CIPŐ, KESZTYŰ', 'slug' => 'munkavedelem']);
    Category::query()->create(['name' => 'ÜRES TÍPUS', 'slug' => 'ures-tipus', 'category_id' => $stocked->id]);

    foreach (['/', route('contact')] as $url) {
        $this->get($url)->assertOk()
            ->assertSee('CSAPÁGYAK')
            ->assertDontSee('MUNKAVÉDELMI CIPŐ, KESZTYŰ')
            ->assertDontSee('ÜRES TÍPUS');
    }
});

it('shows the submenu arrow only on categories that have subcategories', function (): void {
    /** @var TestCase $this */
    $parent = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    Category::query()->create(['name' => 'GOLYÓS CSAPÁGY', 'slug' => 'golyos-csapagy', 'category_id' => $parent->id])
        ->products()->attach(Product::factory()->create());
    $leaf = Category::query()->create(['name' => 'LINEÁRTECHNIKA', 'slug' => 'linear']);
    $leaf->products()->attach(Product::factory()->create());
    $emptyChildOnly = Category::query()->create(['name' => 'BILINCSEK', 'slug' => 'bilincsek']);
    $emptyChildOnly->products()->attach(Product::factory()->create());
    Category::query()->create(['name' => 'ÜRES', 'slug' => 'ures', 'category_id' => $emptyChildOnly->id]);

    $html = (string) $this->blade('<x-category-selector />');

    expect(mb_substr_count($html, 'd="M9 5l7 7-7 7"'))->toBe(1)
        ->and($html)->toContain('LINEÁRTECHNIKA', 'BILINCSEK', 'GOLYÓS CSAPÁGY');
});
