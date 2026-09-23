<?php

declare(strict_types=1);

use App\Models\Category;
use Tests\TestCase;

it('lists the root categories and nested subcategories on the homepage', function (): void {
    /** @var TestCase $this */
    $root = Category::query()->create(['name' => 'Csapágyak', 'slug' => 'csapagyak']);
    Category::query()->create(['name' => 'Tömítések', 'slug' => 'tomitesek']);
    $child = Category::query()->create(['name' => 'Golyóscsapágyak', 'slug' => 'golyoscsapagyak', 'category_id' => $root->id]);
    $grandchild = Category::query()->create(['name' => 'Mélyhornyú', 'slug' => 'melyhornyu', 'category_id' => $child->id]);
    $greatGrandchild = Category::query()->create(['name' => 'Egysoros', 'slug' => 'egysoros', 'category_id' => $grandchild->id]);

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
    Category::query()->create(['name' => Category::BRAND_ROOT_NAME, 'slug' => 'forgalmazott-markaink']);
    Category::query()->create(['name' => 'ZSÍRZÁSTECHNIKA', 'slug' => 'zsirzastechnika']);
    Category::query()->create(['name' => 'BILINCSEK', 'slug' => 'bilincsek']);

    expect(Category::query()->menuRoots()->pluck('name')->all())
        ->toBe(['BILINCSEK', 'ZSÍRZÁSTECHNIKA', Category::BRAND_ROOT_NAME]);

    $this->get('/')->assertOk()->assertSeeInOrder(['BILINCSEK', 'ZSÍRZÁSTECHNIKA', Category::BRAND_ROOT_NAME]);
});

it('opens a fly-out submenu only from its own parent item', function (): void {
    /** @var TestCase $this */
    $root = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    Category::query()->create(['name' => 'GOLYÓS CSAPÁGY', 'slug' => 'golyos-csapagy', 'category_id' => $root->id]);

    $this->get('/')->assertOk()
        ->assertSeeHtml('[&:hover>ul]:visible')
        ->assertDontSeeHtml('group-hover/item:visible');
});

it('shows the real categories in the top menu and no brands link', function (): void {
    /** @var TestCase $this */
    $category = Category::query()->create(['name' => 'TÖMÍTÉSEK', 'slug' => 'tomitesek']);

    $this->get(route('contact'))->assertOk()
        ->assertSeeHtml(route('categories.show', $category))
        ->assertDontSee('Mélyhornyú golyóscsapágyak')
        ->assertDontSee('Márkák');
});
