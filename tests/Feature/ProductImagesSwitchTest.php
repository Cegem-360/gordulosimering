<?php

declare(strict_types=1);

use App\Filament\Pages\Settings\ManageShopSettings;
use App\Livewire\ProductCard;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CategoryTree;
use App\Settings\ShopSettings;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('hides every product photo by default and shows the placeholder instead', function (): void {
    Storage::fake('public');
    $product = Product::factory()->create(['featured_image' => 'products/rossz-kep.jpg', 'images' => ['products/masik.jpg']]);

    expect(resolve(ShopSettings::class)->show_product_images)->toBeFalse()
        ->and($product->image_url)->toBeNull()
        ->and($product->gallery_urls)->toBe([]);

    Livewire::test(ProductCard::class, ['product' => $product])
        ->assertSeeHtml(Vite::asset('resources/images/product-placeholder.svg'))
        ->assertDontSeeHtml('rossz-kep.jpg');
});

it('shows the product photos again once the admin switches them back on', function (): void {
    Storage::fake('public');
    showProductImages();
    $product = Product::factory()->create(['featured_image' => 'products/jo-kep.jpg', 'images' => null]);

    expect($product->image_url)->toBe(Storage::disk('public')->url('products/jo-kep.jpg'));

    Livewire::test(ProductCard::class, ['product' => $product])->assertSeeHtml('jo-kep.jpg');
});

it('keeps uploaded category photos but drops product photos from the category tiles', function (): void {
    Storage::fake('public');
    $withPhoto = Category::query()->create(['name' => 'A', 'slug' => 'a', 'image' => 'categories/a.jpg']);
    $withoutPhoto = Category::query()->create(['name' => 'B', 'slug' => 'b']);
    $withoutPhoto->products()->attach(Product::factory()->create(['featured_image' => 'products/b.jpg']));

    $tree = new CategoryTree();

    expect($tree->coverImageUrl($withPhoto))->toBe(Storage::disk('public')->url('categories/a.jpg'))
        ->and($tree->coverImageUrl($withoutPhoto))->toBeNull();
});

it('lets the admin switch the product photos on and off in the Webshop settings', function (): void {
    actingAs(User::factory()->create(['is_admin' => true]));

    Livewire::test(ManageShopSettings::class)
        ->assertFormSet(['show_product_images' => false])
        ->fillForm(['show_product_images' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(ShopSettings::class)->refresh()->show_product_images)->toBeTrue();
});
