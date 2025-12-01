<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Services\CartService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class CheckOut extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $data = [];

    public Collection $cartItems;

    public ?int $selectedShippingMethod = null;

    public string $selectedPaymentMethod = 'bacs';

    public bool $acceptTerms = false;

    public bool $shipToDifferentAddress = false;

    public function mount(CartService $cartService): void
    {
        $this->cartItems = $cartService->getCartItems();
        $this->form->fill();

        $firstShipping = ShippingMethod::first();
        if ($firstShipping) {
            $this->selectedShippingMethod = $firstShipping->id;
        }
    }

    public function getShippingMethodsProperty(): Collection
    {
        return ShippingMethod::all();
    }

    public function getSelectedShippingProperty(): ?ShippingMethod
    {
        if (! $this->selectedShippingMethod) {
            return null;
        }

        return ShippingMethod::find($this->selectedShippingMethod);
    }

    public function getShippingCostProperty(): float
    {
        return $this->selectedShipping?->cost ?? 0;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Számlázási adatok')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('billing_name')
                            ->label('Név')
                            ->required(),
                        TextInput::make('billing_email')
                            ->label('Email cím')
                            ->email()
                            ->required(),
                        TextInput::make('billing_phone')
                            ->label('Telefonszám')
                            ->tel()
                            ->required(),
                        TextInput::make('billing_company_name')
                            ->label('Cégnév'),
                        TextInput::make('billing_vat_number')
                            ->label('Adószám'),
                        TextInput::make('billing_company_office')
                            ->label('Cégjegyzékszám'),
                    ]),
                Section::make('Számlázási cím')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('billing_postcode')
                            ->label('Irányítószám')
                            ->required(),
                        TextInput::make('billing_city')
                            ->label('Város')
                            ->required(),
                        TextInput::make('billing_address_1')
                            ->label('Utca, házszám')
                            ->columnSpanFull()
                            ->required(),
                        TextInput::make('billing_address_2')
                            ->label('Emelet, ajtó (opcionális)')
                            ->columnSpanFull(),
                        TextInput::make('billing_country')
                            ->label('Ország')
                            ->default('Magyarország')
                            ->required(),
                        TextInput::make('billing_state')
                            ->label('Megye'),
                    ]),
            ])
            ->statePath('data')
            ->model(Order::class);
    }

    public function getSubtotalProperty(): float
    {
        return $this->cartItems->sum(fn ($item) => $item->product->net_selling_price * $item->quantity);
    }

    public function getVatAmountProperty(): float
    {
        return $this->subtotal * 0.27;
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->vatAmount + $this->shippingCost;
    }

    public function getItemCountProperty(): int
    {
        return $this->cartItems->sum('quantity');
    }

    public function getPaymentMethodsProperty(): array
    {
        return [
            'bacs' => [
                'title' => 'Banki átutalás',
                'description' => 'Fizetés közvetlenül a bankszámlánkra. A megjegyzés rovatban tüntesse fel a rendelésszámot. A kiszállítás az összeg beérkezését követően történik.',
                'icon' => 'fa-university',
            ],
            'cod' => [
                'title' => 'Utánvét',
                'description' => 'Fizetés a csomag átvételekor készpénzben vagy bankkártyával a futárnak.',
                'icon' => 'fa-money-bill-wave',
            ],
        ];
    }

    public function create(CartService $cartService): void
    {
        $this->validate([
            'selectedShippingMethod' => 'required|exists:shipping_methods,id',
            'selectedPaymentMethod' => 'required|in:bacs,cod',
            'acceptTerms' => 'accepted',
        ], [
            'selectedShippingMethod.required' => 'Kérjük, válasszon szállítási módot.',
            'selectedPaymentMethod.required' => 'Kérjük, válasszon fizetési módot.',
            'acceptTerms.accepted' => 'El kell fogadnia az Általános Szerződési Feltételeket.',
        ]);

        $data = $this->form->getState();

        $data['user_id'] = Auth::id();
        $data['shipping_method_id'] = $this->selectedShippingMethod;
        $data['payment_method'] = $this->selectedPaymentMethod;
        $data['order_status'] = OrderStatus::PENDING->value;
        $data['order_currency'] = 'HUF';
        $data['payment_method_title'] = $this->paymentMethods[$this->selectedPaymentMethod]['title'];
        $data['set_paid'] = false;
        $data['shipping_tracking_number'] = '';
        $data['shipping_cost'] = $this->shippingCost;

        if ($this->shipToDifferentAddress) {
            $data['shipping_name'] = $this->data['shipping_name'] ?? '';
            $data['shipping_postcode'] = $this->data['shipping_postcode'] ?? '';
            $data['shipping_city'] = $this->data['shipping_city'] ?? '';
            $data['shipping_address_1'] = $this->data['shipping_address_1'] ?? '';
            $data['shipping_address_2'] = $this->data['shipping_address_2'] ?? '';
            $data['shipping_country'] = $this->data['shipping_country'] ?? 'Magyarország';
            $data['shipping_state'] = $this->data['shipping_state'] ?? '';
        } else {
            $data['shipping_name'] = $data['billing_name'] ?? '';
            $data['shipping_postcode'] = $data['billing_postcode'] ?? '';
            $data['shipping_city'] = $data['billing_city'] ?? '';
            $data['shipping_address_1'] = $data['billing_address_1'] ?? '';
            $data['shipping_address_2'] = $data['billing_address_2'] ?? '';
            $data['shipping_country'] = $data['billing_country'] ?? 'Magyarország';
            $data['shipping_state'] = $data['billing_state'] ?? '';
        }

        $record = Order::create($data);

        // Save cart items as order items
        foreach ($this->cartItems as $cartItem) {
            $record->orderItems()->create([
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'total' => $cartItem->product->net_selling_price,
                'subtotal' => $cartItem->product->net_selling_price * $cartItem->quantity,
                'subtotal_tax' => 0,
                'total_tax' => 0,
                'tax_class' => '',
            ]);
        }

        $this->form->model($record)->saveRelationships();

        $cartService->clearCart();

        session(['last_order_id' => $record->id]);
        Notification::make()
            ->title('Sikeres rendelés')
            ->body('Köszönjük a rendelését! Hamarosan felvesszük Önnel a kapcsolatot.')
            ->success()
            ->send();
        $this->redirect(route('thank-you'));
    }

    public function render(): View
    {
        return view('livewire.check-out');
    }
}
