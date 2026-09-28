<?php

declare(strict_types=1);

use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

use Tests\TestCase;

it('no longer lists random products as the most searched ones', function (): void {
    /** @var TestCase $this */
    Product::factory()->count(3)->create();

    $this->get('/')->assertOk()
        ->assertDontSee(['Legkeresettebb termékeink', 'Kiemelt termékeink', 'Kiemelt kategóriáink']);
});

it('shows the featured categories as tiles in menu order, leaving out empty ones', function (): void {
    /** @var TestCase $this */
    $seals = Category::query()->create(['name' => 'TÖMÍTÉSEK', 'slug' => 'tomitesek', 'is_featured' => true, 'sort_order' => 2]);
    $bearings = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak', 'is_featured' => true, 'sort_order' => 1]);
    $empty = Category::query()->create(['name' => 'ÜRES KIEMELT', 'slug' => 'ures', 'is_featured' => true]);
    $notFeatured = Category::query()->create(['name' => 'LINEÁRTECHNIKA', 'slug' => 'linear']);
    foreach ([$seals, $bearings, $notFeatured] as $category) {
        $category->products()->attach(Product::factory()->create());
    }

    $html = $this->get('/')->assertOk()->assertSee('Kiemelt kategóriáink')->getContent();
    $section = mb_substr($html, mb_strpos($html, 'Kiemelt kategóriáink'));
    $section = mb_substr($section, 0, mb_strpos($section, '</section>'));

    expect($section)->toContain(route('categories.show', $bearings), route('categories.show', $seals))
        ->not->toContain('ÜRES KIEMELT')
        ->not->toContain('LINEÁRTECHNIKA')
        ->and(mb_strpos($section, 'CSAPÁGYAK'))->toBeLessThan(mb_strpos($section, 'TÖMÍTÉSEK'));
});

it('shows only the featured web-visible products, up to ten', function (): void {
    /** @var TestCase $this */
    $featured = Product::factory()->create(['name' => 'Kiemelt SKF csapágy', 'is_featured' => true]);
    Product::factory()->create(['name' => 'Rejtett kiemelt', 'is_featured' => true, 'is_web_visible' => false]);
    Product::factory()->create(['name' => 'Sima termék']);
    Product::factory()->count(12)->create(['is_featured' => true]);

    $html = $this->get('/')->assertOk()->assertSee('Kiemelt termékeink')->getContent();

    expect(mb_substr_count($html, 'featured-product-'))->toBe(10)
        ->and($html)->toContain('Kiemelt SKF csapágy')
        ->not->toContain('Rejtett kiemelt')
        ->not->toContain('Sima termék');

    expect($featured->is_featured)->toBeTrue();
});

it('lets the admin mark a category and a product as featured', function (): void {
    actingAs(User::factory()->create(['is_admin' => true]));
    $category = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak']);
    $product = Product::factory()->create();

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['is_featured' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
        ->fillForm(['is_featured' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->refresh()->is_featured)->toBeTrue()
        ->and($product->refresh()->is_featured)->toBeTrue();
});
