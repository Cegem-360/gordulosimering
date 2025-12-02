<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Mail\NewOrderNotificationMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\User;
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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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

    public bool $saveDataForFuture = false;

    public bool $createAccount = false;

    public function mount(CartService $cartService): void
    {
        $this->cartItems = $cartService->getCartItems();

        // Pre-fill form with saved user data (if logged in)
        $user = Auth::user();
        if ($user) {
            $this->form->fill([
                'billing_name' => $user->billing_name ?? $user->name,
                'billing_email' => $user->email,
                'billing_phone' => $user->phone ?? '',
                'billing_company_name' => $user->billing_company_name ?? '',
                'billing_vat_number' => $user->billing_vat_number ?? '',
                'billing_company_office' => $user->billing_company_office ?? '',
                'billing_postcode' => $user->billing_postcode ?? '',
                'billing_city' => $user->billing_city ?? '',
                'billing_address_1' => $user->billing_address_1 ?? '',
                'billing_address_2' => $user->billing_address_2 ?? '',
                'billing_country' => $user->billing_country ?? 'Magyarország',
                'billing_state' => $user->billing_state ?? '',
            ]);

            // Pre-fill shipping data if exists
            if ($user->shipping_name) {
                $this->data['shipping_name'] = $user->shipping_name;
                $this->data['shipping_postcode'] = $user->shipping_postcode ?? '';
                $this->data['shipping_city'] = $user->shipping_city ?? '';
                $this->data['shipping_address_1'] = $user->shipping_address_1 ?? '';
                $this->data['shipping_address_2'] = $user->shipping_address_2 ?? '';
                $this->data['shipping_country'] = $user->shipping_country ?? 'Magyarország';
                $this->data['shipping_state'] = $user->shipping_state ?? '';
            }
        } else {
            // Guest checkout - set defaults
            $this->form->fill([
                'billing_country' => 'Magyarország',
            ]);
        }

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
        $validationRules = [
            'selectedShippingMethod' => 'required|exists:shipping_methods,id',
            'selectedPaymentMethod' => 'required|in:bacs,cod',
            'acceptTerms' => 'accepted',
        ];

        $validationMessages = [
            'selectedShippingMethod.required' => 'Kérjük, válasszon szállítási módot.',
            'selectedPaymentMethod.required' => 'Kérjük, válasszon fizetési módot.',
            'acceptTerms.accepted' => 'El kell fogadnia az Általános Szerződési Feltételeket.',
        ];

        $this->validate($validationRules, $validationMessages);

        $data = $this->form->getState();

        $userId = Auth::id();

        // Create new user if guest selected registration
        if ($this->createAccount && ! Auth::check()) {
            $newUser = User::create([
                'name' => $data['billing_name'],
                'email' => $data['billing_email'],
                'password' => Str::random(32), // Random password - user will set via reset link
                'phone' => $data['billing_phone'] ?? null,
                'billing_name' => $data['billing_name'],
                'billing_company_name' => $data['billing_company_name'] ?? null,
                'billing_vat_number' => $data['billing_vat_number'] ?? null,
                'billing_company_office' => $data['billing_company_office'] ?? null,
                'billing_postcode' => $data['billing_postcode'] ?? null,
                'billing_city' => $data['billing_city'] ?? null,
                'billing_address_1' => $data['billing_address_1'] ?? null,
                'billing_address_2' => $data['billing_address_2'] ?? null,
                'billing_country' => $data['billing_country'] ?? 'Magyarország',
                'billing_state' => $data['billing_state'] ?? null,
            ]);

            $userId = $newUser->id;

            // Send password reset link to user
            Password::sendResetLink(['email' => $newUser->email]);

            // Log in the new user
            Auth::login($newUser);
        }

        $data['user_id'] = $userId;
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

        // Save billing/shipping data to user for future orders (only if logged in and checkbox is checked)
        if ($this->saveDataForFuture && Auth::check()) {
            $user = Auth::user();
            $user->update([
                'phone' => $data['billing_phone'] ?? $user->phone,
                'billing_name' => $data['billing_name'] ?? $user->billing_name,
                'billing_company_name' => $data['billing_company_name'] ?? $user->billing_company_name,
                'billing_vat_number' => $data['billing_vat_number'] ?? $user->billing_vat_number,
                'billing_company_office' => $data['billing_company_office'] ?? $user->billing_company_office,
                'billing_postcode' => $data['billing_postcode'] ?? $user->billing_postcode,
                'billing_city' => $data['billing_city'] ?? $user->billing_city,
                'billing_address_1' => $data['billing_address_1'] ?? $user->billing_address_1,
                'billing_address_2' => $data['billing_address_2'] ?? $user->billing_address_2,
                'billing_country' => $data['billing_country'] ?? $user->billing_country,
                'billing_state' => $data['billing_state'] ?? $user->billing_state,
                'shipping_name' => $data['shipping_name'] ?? $user->shipping_name,
                'shipping_postcode' => $data['shipping_postcode'] ?? $user->shipping_postcode,
                'shipping_city' => $data['shipping_city'] ?? $user->shipping_city,
                'shipping_address_1' => $data['shipping_address_1'] ?? $user->shipping_address_1,
                'shipping_address_2' => $data['shipping_address_2'] ?? $user->shipping_address_2,
                'shipping_country' => $data['shipping_country'] ?? $user->shipping_country,
                'shipping_state' => $data['shipping_state'] ?? $user->shipping_state,
            ]);
        }

        $cartService->clearCart();

        // Send order confirmation email to customer
        Mail::to($record->billing_email)->send(new OrderConfirmationMail($record));

        // Send new order notification to admin
        $adminEmail = config('shop.admin_email');
        if ($adminEmail && $adminEmail !== 'admin@example.com') {
            Mail::to($adminEmail)->send(new NewOrderNotificationMail($record));
        }

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
