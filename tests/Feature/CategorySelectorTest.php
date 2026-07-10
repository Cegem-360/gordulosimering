<?php

declare(strict_types=1);

use App\Models\Category;

it('lists the root categories and nested subcategories on the homepage', function (): void {
    $root = Category::query()->create(['name' => 'Csapágyak', 'slug' => 'csapagyak']);
    Category::query()->create(['name' => 'Tömítések', 'slug' => 'tomitesek']);
    $child = Category::query()->create(['name' => 'Golyóscsapágyak', 'slug' => 'golyoscsapagyak', 'category_id' => $root->id]);
    $grandchild = Category::query()->create(['name' => 'Mélyhornyú', 'slug' => 'melyhornyu', 'category_id' => $child->id]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Csapágyak')
        ->assertSee('Tömítések')
        ->assertSee(route('categories.show', $root), escape: false)
        ->assertSee($child->name)
        ->assertSee(route('categories.show', $child), escape: false)
        ->assertSee($grandchild->name);
});

it('shows a fallback when there are no categories', function (): void {
    $response = $this->get('/');

    $response->assertOk()->assertSee('Nincsenek kategóriák.');
});
