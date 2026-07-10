<?php

declare(strict_types=1);

use App\Models\Category;

it('lists the root categories dynamically on the homepage', function (): void {
    $root = Category::query()->create(['name' => 'Csapágyak', 'slug' => 'csapagyak']);
    Category::query()->create(['name' => 'Tömítések', 'slug' => 'tomitesek']);
    $child = Category::query()->create(['name' => 'Golyóscsapágyak', 'slug' => 'golyoscsapagyak', 'category_id' => $root->id]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Csapágyak')
        ->assertSee('Tömítések')
        ->assertSee(route('categories.show', $root), escape: false)
        ->assertDontSee($child->name);
});

it('shows a fallback when there are no categories', function (): void {
    $response = $this->get('/');

    $response->assertOk()->assertSee('Nincsenek kategóriák.');
});
