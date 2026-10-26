<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\CustomerType;
use App\Livewire\Concerns\HasCustomerDetailsForm;
use App\Mail\QuoteRequestReceivedMail;
use App\Mail\QuoteRequestSubmittedMail;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Services\QuoteList;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Livewire\Component;

/**
 * The Ajánlatkérés page: the products on the visitor's quote list and the
 * form that sends them as one request. The list may be empty; then the
 * message carries what the visitor needs.
 */
final class RequestQuote extends Component implements HasSchemas
{
    use HasCustomerDetailsForm;
    use InteractsWithSchemas;

    public ?array $data = [];

    public ?string $submittedReference = null;

    public function mount(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        $this->form->fill($user ? $this->customerDetailsOf($user) : ['billing_country' => 'Magyarország']);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...$this->customerDetailsSections('Ajánlatkérő', 'kér ajánlatot'),
                Textarea::make('message')
                    ->visible(fn (): bool => $this->hasChosenCustomerType())
                    ->label('Üzenet')
                    ->helperText('Ha a keresett termék nincs a listán, írja le ide (típus, méret, mennyiség).')
                    ->maxLength(5000)
                    ->rows(5)
                    ->columnSpanFull(),
                Checkbox::make('privacy_accepted')
                    ->visible(fn (): bool => $this->hasChosenCustomerType())
                    ->label(new HtmlString('Elolvastam és elfogadom az <a href="' . route('privacy-policy') . '" target="_blank" class="text-blue-600 underline">Adatvédelmi nyilatkozatot</a>.'))
                    ->accepted()
                    ->validationMessages(['accepted' => 'Az ajánlatkéréshez el kell fogadnia az Adatvédelmi nyilatkozatot.'])
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    /**
     * From the − / + buttons or the typed quantity (a string, possibly empty).
     */
    public function updateQuantity(int $productId, int|string $quantity, QuoteList $quoteList): void
    {
        $quantity = (int) $quantity;

        $product = Product::query()->find($productId);

        if ($product === null || ! $quoteList->has($productId)) {
            return;
        }

        $quoteList->update($product, $quantity);
        $this->dispatch('quoteListUpdated');
    }

    public function removeItem(int $productId, QuoteList $quoteList): void
    {
        $quoteList->remove($productId);
        $this->dispatch('quoteListUpdated');
    }

    public function submit(QuoteList $quoteList): void
    {
        $data = $this->form->getState();
        $lines = $quoteList->lines();

        if ($lines->isEmpty() && blank($data['message'] ?? null)) {
            $this->addError('data.message', 'Írja le, mire kér ajánlatot, vagy tegyen termékeket az ajánlatkérésbe.');

            return;
        }

        $quoteRequest = DB::transaction(function () use ($data, $lines): QuoteRequest {
            $isCompany = $data['customer_type'] === CustomerType::Company;

            $quoteRequest = QuoteRequest::query()->create([
                'user_id' => auth()->id(),
                'customer_type' => $data['customer_type'],
                'name' => $data['billing_name'],
                'email' => $data['billing_email'],
                'phone' => $data['billing_phone'],
                'company' => $isCompany ? $data['billing_company_name'] : null,
                'vat_number' => $isCompany ? $data['billing_vat_number'] : null,
                'postcode' => $data['billing_postcode'],
                'city' => $data['billing_city'],
                'address_1' => $data['billing_address_1'],
                'address_2' => $data['billing_address_2'] ?: null,
                'country' => $data['billing_country'],
                'state' => $data['billing_state'] ?: null,
                'message' => $data['message'] ?: null,
            ]);

            foreach ($lines as $line) {
                $quoteRequest->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'product_code' => $line['product']->product_code,
                    'unit_price' => $line['product']->unit_price ?: null,
                    'quantity' => $line['quantity'],
                ]);
            }

            return $quoteRequest;
        });

        $quoteRequest->load('items');

        Mail::to($quoteRequest->email)->send(new QuoteRequestReceivedMail($quoteRequest));

        if (filled(config('shop.admin_email'))) {
            Mail::to(config('shop.admin_email'))->send(new QuoteRequestSubmittedMail($quoteRequest));
        }

        $quoteList->clear();
        $this->dispatch('quoteListUpdated');

        $this->submittedReference = $quoteRequest->reference;
    }

    public function render(QuoteList $quoteList): Factory|View
    {
        return view('livewire.pages.request-quote', [
            'lines' => $quoteList->lines(),
        ]);
    }
}
