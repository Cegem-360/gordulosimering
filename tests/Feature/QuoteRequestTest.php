<?php

declare(strict_types=1);

use App\Enums\CustomerType;
use App\Enums\QuoteRequestStatus;
use App\Filament\Resources\QuoteRequests\Pages\EditQuoteRequest;
use App\Filament\Resources\QuoteRequests\Pages\ListQuoteRequests;
use App\Filament\Resources\QuoteRequests\Pages\ViewQuoteRequest;
use App\Livewire\Pages\RequestQuote;
use App\Livewire\ProductCard;
use App\Livewire\Products\Show;
use App\Livewire\QuoteListIcon;
use App\Mail\QuoteRequestReceivedMail;
use App\Mail\QuoteRequestSubmittedMail;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Services\QuoteList;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Number;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function quoteFormData(array $overrides = []): array
{
    return [
        'customer_type' => CustomerType::Company->value,
        'billing_name' => 'Kovács Péter',
        'billing_email' => 'peter@example.test',
        'billing_phone' => '+36 30 123 4567',
        'billing_company_name' => 'Példa Kft.',
        'billing_vat_number' => '12345678-2-42',
        'billing_postcode' => '1102',
        'billing_city' => 'Budapest',
        'billing_address_1' => 'Kőrösi Csoma S. út 18.',
        'billing_country' => 'Magyarország',
        'message' => 'Sürgős lenne.',
        'privacy_accepted' => true,
        ...$overrides,
    ];
}

it('puts an out-of-stock product on the quote list from its card', function (): void {
    $product = Product::factory()->create(['stock_quantity' => 0]);

    Livewire::test(ProductCard::class, ['product' => $product])
        ->call('addToQuote')
        ->assertDispatched('quoteListUpdated')
        ->assertSee('Ajánlatkérésben')
        ->assertSeeHtml('href="' . route('quote-request') . '"');

    expect(resolve(QuoteList::class)->items())->toBe([$product->id => 1]);
    Livewire::test(QuoteListIcon::class)->assertSet('itemCount', 1);
});

it('puts the quantity field\'s default on the quote list, one order unit', function (): void {
    $product = Product::factory()->create(['stock_quantity' => 0, 'order_unit' => 10, 'min_order_quantity' => 3]);

    Livewire::test(Show::class, ['product' => $product])->call('addToQuote');

    expect(resolve(QuoteList::class)->items())->toBe([$product->id => 10]);
});

it('lists the collected products on the Ajánlatkérés page and takes any quantity', function (): void {
    $product = Product::factory()->create(['name' => 'SKF 6204-2RSH', 'order_unit' => 10, 'min_order_quantity' => 10]);
    resolve(QuoteList::class)->add($product);

    get(route('quote-request'))->assertOk()->assertSee(['Ajánlatkérés', 'SKF 6204-2RSH']);

    Livewire::test(RequestQuote::class)
        ->call('updateQuantity', $product->id, 13)
        ->assertDispatched('quoteListUpdated');
    expect(resolve(QuoteList::class)->items())->toBe([$product->id => 13]);

    Livewire::test(RequestQuote::class)->call('updateQuantity', $product->id, '');
    expect(resolve(QuoteList::class)->items())->toBe([$product->id => 1]);

    Livewire::test(RequestQuote::class)->call('removeItem', $product->id);
    expect(resolve(QuoteList::class)->items())->toBe([]);
});

it('ignores quantity changes for products not on the list', function (): void {
    $product = Product::factory()->create();

    Livewire::test(RequestQuote::class)->call('updateQuantity', $product->id, 5);

    expect(resolve(QuoteList::class)->items())->toBe([]);
});

it('sends the quote request to the shop and confirms it to the visitor', function (): void {
    Mail::fake();
    $product = Product::factory()->create(['name' => 'TENTE befeszítőcsap', 'product_code' => 'TE CSAP R47', 'net_selling_price' => 2219, 'is_on_sale' => false]);
    resolve(QuoteList::class)->add($product);
    resolve(QuoteList::class)->update($product, 4);

    Livewire::test(RequestQuote::class)
        ->fillForm(quoteFormData())
        ->call('submit')
        ->assertHasNoFormErrors()
        ->assertSee('Köszönjük ajánlatkérését!')
        ->assertDispatched('quoteListUpdated');

    $quoteRequest = QuoteRequest::query()->with('items')->sole();
    expect($quoteRequest)
        ->customer_type->toBe(CustomerType::Company)
        ->name->toBe('Kovács Péter')
        ->company->toBe('Példa Kft.')
        ->vat_number->toBe('12345678-2-42')
        ->address()->toBe('1102 Budapest, Kőrösi Csoma S. út 18., Magyarország')
        ->status->toBe(QuoteRequestStatus::New)
        ->reference->toStartWith('AK-')
        ->and($quoteRequest->items->sole())
        ->product_name->toBe('TENTE befeszítőcsap')
        ->product_code->toBe('TE CSAP R47')
        ->quantity->toBe(4)
        ->and((float) $quoteRequest->items->sole()->unit_price)->toBe(2219.0)
        ->and(resolve(QuoteList::class)->items())->toBe([]);

    Mail::assertQueued(QuoteRequestReceivedMail::class, fn ($mail): bool => $mail->hasTo('peter@example.test') && $mail->hasReplyTo('gs@gordulo-simmering.hu'));
    Mail::assertQueued(QuoteRequestSubmittedMail::class, fn ($mail): bool => $mail->hasTo('gs@gordulo-simmering.hu') && $mail->hasReplyTo('peter@example.test'));
});

it('renders both emails with the requested products', function (): void {
    $quoteRequest = QuoteRequest::factory()->create(['message' => 'Kérem a szállítási időt is.']);
    $quoteRequest->items()->create(['product_name' => 'SKF 6204-2RSH', 'product_code' => '6204-2RSH', 'unit_price' => 1500, 'quantity' => 10]);
    $quoteRequest->load('items');

    expect((new QuoteRequestReceivedMail($quoteRequest))->render())
        ->toContain('SKF 6204-2RSH', '10 db', 'Kérem a szállítási időt is.', $quoteRequest->reference)
        ->not->toContain('Listaáron');
    expect((new QuoteRequestSubmittedMail($quoteRequest))->render())
        ->toContain('SKF 6204-2RSH', $quoteRequest->email, 'Magánszemély', $quoteRequest->city, 'Listaáron összesen', Number::currency(15000, 'HUF', 'hu', 0));
});

it('accepts a request with only a message, but not an empty one', function (): void {
    Mail::fake();

    Livewire::test(RequestQuote::class)
        ->fillForm(quoteFormData(['message' => '']))
        ->call('submit')
        ->assertHasErrors('data.message');
    expect(QuoteRequest::query()->count())->toBe(0);

    Livewire::test(RequestQuote::class)
        ->fillForm(quoteFormData(['message' => '20 db 6204 csapágy']))
        ->call('submit')
        ->assertHasNoFormErrors();
    expect(QuoteRequest::query()->sole()->items)->toBeEmpty();
});

it('requires the contact details and the privacy acceptance', function (): void {
    Livewire::test(RequestQuote::class)
        ->fillForm(quoteFormData(['billing_name' => '', 'billing_email' => 'nem-email', 'billing_phone' => '', 'billing_postcode' => '', 'privacy_accepted' => false]))
        ->call('submit')
        ->assertHasFormErrors(['billing_name' => 'required', 'billing_email' => 'email', 'billing_phone' => 'required', 'billing_postcode' => 'required', 'privacy_accepted' => 'accepted']);
});

it('fills in a logged-in customer and links the request to them', function (): void {
    Mail::fake();
    $user = User::factory()->create([
        'name' => 'Nagy Anna',
        'phone' => '+36 1 234 5678',
        'customer_type' => CustomerType::Company,
        'billing_company_name' => 'Anna Bt.',
        'billing_vat_number' => '87654321-1-13',
        'billing_postcode' => '6720',
        'billing_city' => 'Szeged',
        'billing_address_1' => 'Kárász u. 1.',
        'billing_country' => 'Magyarország',
    ]);
    actingAs($user);

    Livewire::test(RequestQuote::class)
        ->assertSchemaStateSet([
            'customer_type' => CustomerType::Company,
            'billing_name' => 'Nagy Anna',
            'billing_email' => $user->email,
            'billing_phone' => '+36 1 234 5678',
            'billing_company_name' => 'Anna Bt.',
            'billing_vat_number' => '87654321-1-13',
            'billing_city' => 'Szeged',
        ])
        ->fillForm(['message' => 'Ár kérése', 'privacy_accepted' => true])
        ->call('submit')
        ->assertHasNoFormErrors();

    expect(QuoteRequest::query()->sole()->user_id)->toBe($user->id);
});

it('asks private person or company first, and the details only after that', function (): void {
    Livewire::test(RequestQuote::class)
        ->assertSee(['Ajánlatkérő', 'Magánszemély', 'Cég'])
        ->assertDontSee(['Számlázási adatok', 'Számlázási cím', 'Ajánlatkérés elküldése'])
        ->set('data.customer_type', CustomerType::Private->value)
        ->assertSee(['Számlázási adatok', 'Számlázási cím', 'Ajánlatkérés elküldése'])
        ->assertDontSee('Adószám');
});

it('requires the company name and VAT number from a company only', function (): void {
    Mail::fake();

    Livewire::test(RequestQuote::class)
        ->fillForm(quoteFormData(['billing_company_name' => '', 'billing_vat_number' => '']))
        ->call('submit')
        ->assertHasFormErrors(['billing_company_name' => 'required', 'billing_vat_number' => 'required']);

    Livewire::test(RequestQuote::class)
        ->fillForm(quoteFormData(['customer_type' => CustomerType::Private->value]))
        ->call('submit')
        ->assertHasNoFormErrors();

    expect(QuoteRequest::query()->sole())
        ->customer_type->toBe(CustomerType::Private)
        ->company->toBeNull()
        ->vat_number->toBeNull();
});

it('requires the choice of private person or company', function (): void {
    Livewire::test(RequestQuote::class)
        ->fillForm(quoteFormData(['customer_type' => null]))
        ->call('submit')
        ->assertHasFormErrors(['customer_type' => 'required']);

    expect(QuoteRequest::query()->count())->toBe(0);
});

it('lets the admin see and handle quote requests', function (): void {
    actingAs(User::factory()->create(['is_admin' => true]));
    $quoteRequest = QuoteRequest::factory()->company()->create(['name' => 'Kovács Péter', 'vat_number' => '12345678-2-42', 'city' => 'Budapest']);
    $quoteRequest->items()->create(['product_name' => 'SKF 6204-2RSH', 'quantity' => 10, 'unit_price' => 1500]);
    QuoteRequest::factory()->handled()->create();

    Livewire::test(ListQuoteRequests::class)->assertCanSeeTableRecords([$quoteRequest]);
    Livewire::test(ViewQuoteRequest::class, ['record' => $quoteRequest->getRouteKey()])
        ->assertSee(['Kovács Péter', 'Cég', '12345678-2-42', 'Budapest', 'SKF 6204-2RSH', Number::currency(1500, 'HUF', 'hu', 0)]);
    Livewire::test(EditQuoteRequest::class, ['record' => $quoteRequest->getRouteKey()])
        ->fillForm(['status' => QuoteRequestStatus::Quoted->value, 'admin_note' => 'Elküldve e-mailben'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($quoteRequest->refresh()->status)->toBe(QuoteRequestStatus::Quoted)
        ->and(QuoteRequest::query()->open()->count())->toBe(0);
});

it('puts the quantity set on the product page on the quote list', function (bool $inStock): void {
    $product = Product::factory()->create(['stock_quantity' => $inStock ? 50 : 0]);

    Livewire::test(Show::class, ['product' => $product])
        ->set('quantity', 7)
        ->call('addToQuote');

    expect(resolve(QuoteList::class)->items())->toBe([$product->id => 7]);
})->with(['in stock' => true, 'out of stock' => false]);
