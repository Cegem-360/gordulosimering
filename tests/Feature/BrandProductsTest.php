<?php

declare(strict_types=1);

use App\Livewire\Products\Categories\Index as CategoriesIndex;
use App\Livewire\Products\Index as ProductsIndex;
use App\Models\Product;
use Livewire\Livewire;

use function Pest\Laravel\get;

/**
 * @param  array<string, mixed>  $attributes
 */
function brandProduct(array $attributes): Product
{
    return Product::factory()->create(['size' => null, 'stock_quantity' => 0, ...$attributes]);
}

it('counts a brand only by a whole word of the product name, not by the code or the size', function (): void {
    brandProduct(['product_code' => 'KÉZKRÉM CFA 100ML', 'name' => 'CFA körömvirágos kézkrém']);
    brandProduct(['product_code' => 'SKF-1', 'name' => 'SKF/CFA teszt']);
    brandProduct(['product_code' => 'UCFA 205_KBS', 'name' => 'KELETI Y csapágyegység']);
    brandProduct(['product_code' => 'X-1', 'name' => 'UCFA tartó', 'size' => 'CFA']);

    expect(Product::query()->ofCatalogBrand(['name_word' => 'CFA'])->pluck('product_code')->sort()->values()->all())
        ->toBe(['KÉZKRÉM CFA 100ML', 'SKF-1']);
});

it('finds KBS by its product code mark, not by a size like KBSE-25', function (): void {
    brandProduct(['product_code' => '6403-2Z_KBS', 'name' => 'KELETI egysorú mélyhornyú golyóscsapágy']);
    brandProduct(['product_code' => 'UC 204_KBS (YAR 204)', 'name' => 'KELETI Y-csapágy']);
    brandProduct(['product_code' => '0668-025-00_REX', 'name' => 'REXROTH lineáris golyóscsapágy', 'size' => 'KBSE-25']);
    brandProduct(['product_code' => 'KBS-20-PP_I', 'name' => 'INA lineáris csapágy']);

    expect(Product::query()->ofCatalogBrand(['code_part' => '_KBS'])->pluck('product_code')->sort()->values()->all())
        ->toBe(['6403-2Z_KBS', 'UC 204_KBS (YAR 204)']);
});

it('treats LIKE wildcards in a brand word literally', function (): void {
    brandProduct(['name' => 'WD-40 spray']);
    brandProduct(['name' => 'WDX40 spray']);

    expect(Product::query()->ofCatalogBrand(['name_word' => 'WD-40'])->count())->toBe(1)
        ->and(Product::query()->ofCatalogBrand(['name_word' => 'W_-40'])->count())->toBe(0);
});

it('links CFA from the Márkáink page to its own products', function (): void {
    brandProduct(['name' => 'CFA körömvirágos kézkrém']);
    brandProduct(['product_code' => 'UCFA 205_KBS', 'name' => 'KELETI Y csapágyegység']);

    get(route('brands'))->assertOk()->assertSeeHtml('href="' . route('products.index', ['gyarto' => 'CFA']) . '"');

    Livewire::withQueryParams(['gyarto' => 'CFA'])
        ->test(ProductsIndex::class)
        ->assertSee('CFA körömvirágos kézkrém')
        ->assertDontSee('UCFA 205_KBS');
});

it('still finds the product code matches in the product search', function (): void {
    brandProduct(['name' => 'CFA körömvirágos kézkrém']);
    brandProduct(['product_code' => 'UCFA 205_KBS', 'name' => 'KELETI Y csapágyegység']);

    Livewire::withQueryParams(['search' => 'CFA'])
        ->test(ProductsIndex::class)
        ->assertSee('CFA körömvirágos kézkrém')
        ->assertSee('KELETI Y csapágyegység');
});

it('lists a brand by product code, numbers first, ignoring stock', function (): void {
    brandProduct(['product_code' => 'YATO B', 'name' => 'YATO bé', 'stock_quantity' => 5]);
    brandProduct(['product_code' => 'YATO A', 'name' => 'YATO á']);
    brandProduct(['product_code' => '100 YATO', 'name' => 'YATO száz']);

    $codes = Livewire::withQueryParams(['gyarto' => 'YATO'])
        ->test(ProductsIndex::class)
        ->instance()->products->pluck('product_code')->all();

    expect($codes)->toBe(['100 YATO', 'YATO A', 'YATO B']);
});

it('lists a filtered brand by product code too', function (): void {
    foreach (['NU 2', '6204', 'BA 1'] as $code) {
        brandProduct(['product_code' => $code])->forceFill(['brand' => 'SKF'])->saveQuietly();
    }

    $codes = Livewire::withQueryParams(['marka' => 'SKF'])
        ->test(CategoriesIndex::class)
        ->instance()->products->pluck('product_code')->all();

    expect($codes)->toBe(['6204', 'BA 1', 'NU 2']);
});
