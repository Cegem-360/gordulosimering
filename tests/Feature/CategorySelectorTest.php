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
        ->assertSee(route('categories.show', $root), escape: false)
        ->assertSee($child->name)
        ->assertSee(route('categories.show', $child), escape: false)
        ->assertSee($grandchild->name)
        ->assertSee($greatGrandchild->name); // 4th level renders through the recursive fly-out
});

it('shows a fallback when there are no categories', function (): void {
    /** @var TestCase $this */
    $response = $this->get('/');

    $response->assertOk()->assertSee('Nincsenek kategóriák.');
});
