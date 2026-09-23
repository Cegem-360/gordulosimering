<?php

declare(strict_types=1);

use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    actingAs(User::factory()->create());
});

it('renders the reworked product create and edit forms', function (): void {
    $product = Product::factory()->create();

    get('admin/products/create')->assertSuccessful();
    get('admin/products/' . $product->getKey() . '/edit')->assertSuccessful();
    get('admin/products/' . $product->getKey())->assertSuccessful();
});

it('renders the reworked category create and edit forms', function (): void {
    $category = Category::query()->create(['name' => 'Teszt', 'slug' => 'teszt']);

    get('admin/categories/create')->assertSuccessful();
    get('admin/categories/' . $category->getKey() . '/edit')->assertSuccessful();
});

it('persists product image, document and category assignments', function (): void {
    $category = Category::query()->create(['name' => 'Csapágyak', 'slug' => 'csapagyak']);
    $product = Product::factory()->create([
        'images' => ['products/images/a.jpg'],
        'documents' => ['products/documents/datasheet.pdf'],
    ]);
    $product->categories()->attach($category);

    expect($product->fresh()->images)->toBe(['products/images/a.jpg'])
        ->and($product->fresh()->documents)->toBe(['products/documents/datasheet.pdf'])
        ->and($product->categories()->pluck('name')->all())->toBe(['Csapágyak']);
});

it('resolves the featured image as the primary image and gallery', function (): void {
    $product = Product::factory()->create([
        'featured_image' => 'products/featured/main.jpg',
        'images' => ['products/images/extra.jpg'],
    ]);

    expect($product->image)->toBe('products/featured/main.jpg')
        ->and($product->image_url)->toContain('/storage/products/featured/main.jpg')
        ->and($product->gallery_urls)->toHaveCount(2)
        ->and($product->gallery_urls[0])->toContain('products/featured/main.jpg');

    $external = Product::factory()->create(['featured_image' => null, 'images' => ['https://cdn.test/x.jpg']]);

    expect($external->image_url)->toBe('https://cdn.test/x.jpg');
});

it('saves a category photo and menu position from the admin form', function (): void {
    Storage::fake('public');
    $category = Category::query()->create(['name' => 'NORMA BENZINCSŐBILINCS', 'slug' => 'norma-benzin']);

    Livewire::test(EditCategory::class, ['record' => $category->getKey()])
        ->fillForm([
            'sort_order' => 3,
            'image' => UploadedFile::fake()->image('benzin.jpg'),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $category->refresh();
    expect($category->sort_order)->toBe(3)
        ->and($category->image)->toStartWith('categories/');
    Storage::disk('public')->assertExists($category->image);
});

it('lists categories in menu order with their parent and lets them be reordered', function (): void {
    $root = Category::query()->create(['name' => 'CSAPÁGYAK', 'slug' => 'csapagyak', 'sort_order' => 1]);
    $second = Category::query()->create(['name' => 'GÖRGŐS CSAPÁGY', 'slug' => 'gorgos', 'category_id' => $root->id, 'sort_order' => 3]);
    $first = Category::query()->create(['name' => 'GOLYÓS CSAPÁGY', 'slug' => 'golyos', 'category_id' => $root->id, 'sort_order' => 2]);

    Livewire::test(ListCategories::class)
        ->assertCanSeeTableRecords([$root, $first, $second], inOrder: true)
        ->assertTableColumnStateSet('parentCategory.name', 'CSAPÁGYAK', $first)
        ->filterTable('category_id', $root->getKey())
        ->assertCanSeeTableRecords([$first, $second])
        ->assertCanNotSeeTableRecords([$root]);
});
