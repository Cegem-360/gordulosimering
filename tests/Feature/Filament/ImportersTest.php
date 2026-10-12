<?php

declare(strict_types=1);

use App\Enums\CsvDelimiter;
use App\Enums\CustomerType;
use App\Filament\Imports\CategoryImporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Imports\UserImporter;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductCsvExporter;
use Filament\Actions\ImportAction;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

describe('product importer', function (): void {
    it('updates a product by product code with Hungarian values', function (): void {
        $product = Product::factory()->create(['product_code' => 'A-1', 'is_web_visible' => true, 'net_selling_price' => 100]);

        ProductImporter::test(columnMap: ['product_code' => 'product_code', 'is_web_visible' => 'is_web_visible', 'net_selling_price' => 'net_selling_price', 'minimum_stock' => 'minimum_stock'])
            ->import(['product_code' => 'A-1', 'is_web_visible' => 'Nem', 'net_selling_price' => '1 234,50', 'minimum_stock' => '5'])
            ->assertImported();

        expect($product->fresh())
            ->is_web_visible->toBeFalse()
            ->net_selling_price->toBe('1234.50')
            ->minimum_stock->toBe(5)
            ->and(Product::query()->count())->toBe(1);
    });

    it('creates a new product with a unique slug and its categories', function (): void {
        Product::factory()->create(['slug' => 'b-2']);
        Category::query()->create(['name' => 'Csapágyak', 'slug' => 'csapagyak']);
        Category::query()->create(['name' => 'Golyóscsapágyak', 'slug' => 'golyoscsapagyak']);

        $product = ProductImporter::test(columnMap: ['product_code' => 'product_code', 'name' => 'name', 'images' => 'images', 'categories' => 'categories'])
            ->import(['product_code' => 'B-2', 'name' => 'Csapágy 6204', 'images' => '["products/images/a.jpg"]', 'categories' => 'Csapágyak, Golyóscsapágyak'])
            ->assertImported()
            ->getRecord();

        expect($product->fresh())
            ->slug->toBe('b-2-1')
            ->images->toBe(['products/images/a.jpg'])
            ->and($product->categories()->pluck('name')->sort()->values()->all())->toBe(['Csapágyak', 'Golyóscsapágyak']);
    });

    it('rejects invalid values and unknown categories', function (array $row, string $field): void {
        ProductImporter::test(columnMap: array_combine(array_keys($row), array_keys($row)))
            ->import($row)
            ->assertHasErrors([$field]);
    })->with([
        'yes/no' => [['product_code' => 'X', 'name' => 'X', 'is_web_visible' => 'talán'], 'is_web_visible'],
        'number' => [['product_code' => 'X', 'name' => 'X', 'net_selling_price' => 'sok'], 'net_selling_price'],
        'integer' => [['product_code' => 'X', 'name' => 'X', 'minimum_stock' => '1,5'], 'minimum_stock'],
        'category' => [['product_code' => 'X', 'name' => 'X', 'categories' => 'Nincs ilyen'], 'categories'],
    ]);

    it('fails a row whose slug belongs to another product', function (): void {
        Product::factory()->create(['slug' => 'foglalt']);
        Product::factory()->create(['product_code' => 'C-3']);

        ProductImporter::test(columnMap: ['product_code' => 'product_code', 'slug' => 'slug'])
            ->import(['product_code' => 'C-3', 'slug' => 'foglalt'])
            ->assertHasRowFailure('A(z) „foglalt” URL-kulcs már egy másik termékhez tartozik.');
    });
});

describe('category importer', function (): void {
    it('updates by slug, links the parent by name and keeps is_featured when blank', function (): void {
        $parent = Category::query()->create(['name' => 'Csapágyak', 'slug' => 'csapagyak']);
        $category = Category::query()->create(['name' => 'Régi', 'slug' => 'golyos', 'is_featured' => true]);

        CategoryImporter::test()
            ->import(['name' => 'Golyóscsapágyak', 'slug' => 'golyos', 'parentCategory' => 'Csapágyak', 'sort_order' => '3', 'is_featured' => '', 'description' => '', 'display' => '', 'image' => ''])
            ->assertImported();

        expect($category->fresh())
            ->name->toBe('Golyóscsapágyak')
            ->category_id->toBe($parent->id)
            ->sort_order->toBe(3)
            ->is_featured->toBeTrue()
            ->and(Category::query()->count())->toBe(2);
    });

    it('creates a category with a generated slug', function (): void {
        $category = CategoryImporter::test(columnMap: ['name' => 'name', 'is_featured' => 'is_featured'])
            ->import(['name' => 'Tömítő gyűrűk', 'is_featured' => 'igen'])
            ->assertImported()
            ->getRecord();

        expect($category->fresh())->slug->toBe('tomito-gyuruk')->is_featured->toBeTrue();
    });

    it('rejects an unknown parent and a category set as its own parent', function (): void {
        CategoryImporter::test(columnMap: ['name' => 'name', 'parentCategory' => 'parentCategory'])
            ->import(['name' => 'Új', 'parentCategory' => 'Nincs ilyen'])
            ->assertHasErrors(['parentCategory']);

        Category::query()->create(['name' => 'Önmaga', 'slug' => 'onmaga']);

        CategoryImporter::test(columnMap: ['slug' => 'slug', 'parentCategory' => 'parentCategory'])
            ->import(['slug' => 'onmaga', 'parentCategory' => 'onmaga'])
            ->assertHasRowFailure('A kategória nem lehet a saját szülője.');
    });
});

describe('user importer', function (): void {
    it('updates a user by email without touching the password or admin flag', function (): void {
        $user = User::factory()->create(['email' => 'anna@example.com', 'password' => 'titok123', 'base_discount_percentage' => 10]);

        UserImporter::test(columnMap: ['email' => 'email', 'name' => 'name', 'customer_type' => 'customer_type', 'base_discount_percentage' => 'base_discount_percentage', 'billing_city' => 'billing_city'])
            ->import(['email' => 'anna@example.com', 'name' => 'Kovács Anna', 'customer_type' => 'Cég', 'base_discount_percentage' => '', 'billing_city' => 'Szeged'])
            ->assertImported();

        $user->refresh();

        expect($user)
            ->name->toBe('Kovács Anna')
            ->customer_type->toBe(CustomerType::Company)
            ->base_discount_percentage->toBe('10.00')
            ->billing_city->toBe('Szeged')
            ->is_admin->toBeFalse()
            ->and(Hash::check('titok123', $user->password))->toBeTrue();
    });

    it('creates a customer with a random password', function (): void {
        $user = UserImporter::test(columnMap: ['email' => 'email', 'name' => 'name', 'customer_type' => 'customer_type'])
            ->import(['email' => 'uj@example.com', 'name' => 'Új Vevő', 'customer_type' => 'private'])
            ->assertImported()
            ->getRecord();

        expect($user->fresh())
            ->customer_type->toBe(CustomerType::Private)
            ->is_admin->toBeFalse()
            ->password->not->toBeEmpty();
    });

    it('rejects an invalid email, customer type or discount', function (array $row, string $field): void {
        UserImporter::test(columnMap: array_combine(array_keys($row), array_keys($row)))
            ->import($row)
            ->assertHasErrors([$field]);
    })->with([
        'email' => [['email' => 'nem-email', 'name' => 'X'], 'email'],
        'customer type' => [['email' => 'x@example.com', 'name' => 'X', 'customer_type' => 'alapítvány'], 'customer_type'],
        'discount' => [['email' => 'x@example.com', 'name' => 'X', 'base_discount_percentage' => '150'], 'base_discount_percentage'],
    ]);
});

it('dispatches the import from each admin list', function (string $page, string $importer, string $csv): void {
    actingAs(User::factory()->admin()->create());
    $imports = ImportAction::fake();

    Livewire::test($page)
        ->mountAction('import')
        ->fillForm(['file' => UploadedFile::fake()->createWithContent('import.csv', $csv)])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    $imports->assertDispatchedTimes($importer);
})->with([
    'products' => [ListProducts::class, ProductImporter::class, "Termékkód;Terméknév\nA-1;Csapágy\n"],
    'categories' => [ListCategories::class, CategoryImporter::class, "Név;URL-kulcs\nCsapágyak;csapagyak\n"],
    'users' => [ListUsers::class, UserImporter::class, "Név;E-mail cím\nAnna;anna@example.com\n"],
]);

it('detects the delimiter and maps the columns of a re-uploaded product export', function (CsvDelimiter $delimiter): void {
    actingAs(User::factory()->admin()->create());
    Product::factory()->create(['product_code' => 'EXP-1', 'name' => 'Csapágy']);

    $handle = fopen('php://memory', 'w+b');
    resolve(ProductCsvExporter::class)->write($handle, Product::query(), $delimiter);
    rewind($handle);

    $imports = ImportAction::fake();

    Livewire::test(ListProducts::class)
        ->mountAction('import')
        ->fillForm(['file' => UploadedFile::fake()->createWithContent('termekek.' . $delimiter->extension(), stream_get_contents($handle))])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    $imports->assertDispatched(ProductImporter::class, fn (Import $import, array $columnMap): bool => ! in_array(null, $columnMap, true)
        && $columnMap['product_code'] === 'Termékkód (product_code)'
        && $columnMap['name'] === 'Név (name)'
        && $columnMap['is_web_visible'] === 'Webshopban látszik (is_web_visible)'
        && $columnMap['categories'] === 'Kategóriák (categories)');
})->with([CsvDelimiter::Semicolon, CsvDelimiter::Comma, CsvDelimiter::Tab]);

it('imports a tab-separated file end to end', function (): void {
    actingAs(User::factory()->admin()->create());
    $product = Product::factory()->create(['product_code' => 'TAB-1', 'name' => 'Régi', 'net_selling_price' => 100]);

    Livewire::test(ListProducts::class)
        ->mountAction('import')
        ->fillForm(['file' => UploadedFile::fake()->createWithContent('termekek.txt', "\u{FEFF}\"Termékkód (product_code)\"\t\"Név (name)\"\t\"Nettó eladási ár (net_selling_price)\"\nTAB-1\tÚj név\t1 234,50\n")])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect($product->fresh())->name->toBe('Új név')->net_selling_price->toBe('1234.50');
});
