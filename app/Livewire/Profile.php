<?php

declare(strict_types=1);

namespace App\Livewire;

use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

final class Profile extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public ?string $current_password = '';

    public ?string $new_password = '';

    public ?string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'billing_name' => $user->billing_name ?? '',
            'billing_company_name' => $user->billing_company_name ?? '',
            'billing_vat_number' => $user->billing_vat_number ?? '',
            'billing_company_office' => $user->billing_company_office ?? '',
            'billing_postcode' => $user->billing_postcode ?? '',
            'billing_city' => $user->billing_city ?? '',
            'billing_address_1' => $user->billing_address_1 ?? '',
            'billing_address_2' => $user->billing_address_2 ?? '',
            'billing_country' => $user->billing_country ?? 'Magyarország',
            'billing_state' => $user->billing_state ?? '',
            'shipping_name' => $user->shipping_name ?? '',
            'shipping_postcode' => $user->shipping_postcode ?? '',
            'shipping_city' => $user->shipping_city ?? '',
            'shipping_address_1' => $user->shipping_address_1 ?? '',
            'shipping_address_2' => $user->shipping_address_2 ?? '',
            'shipping_country' => $user->shipping_country ?? 'Magyarország',
            'shipping_state' => $user->shipping_state ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Személyes adatok')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Név')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email cím')
                            ->email()
                            ->required()
                            ->disabled(),
                        TextInput::make('phone')
                            ->label('Telefonszám')
                            ->tel(),
                    ]),
                Section::make('Számlázási adatok')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('billing_name')
                            ->label('Számlázási név'),
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
                            ->label('Irányítószám'),
                        TextInput::make('billing_city')
                            ->label('Város'),
                        TextInput::make('billing_address_1')
                            ->label('Utca, házszám')
                            ->columnSpanFull(),
                        TextInput::make('billing_address_2')
                            ->label('Emelet, ajtó (opcionális)')
                            ->columnSpanFull(),
                        TextInput::make('billing_country')
                            ->label('Ország')
                            ->default('Magyarország'),
                        TextInput::make('billing_state')
                            ->label('Megye'),
                    ]),
                Section::make('Szállítási cím')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('shipping_name')
                            ->label('Címzett neve'),
                        TextInput::make('shipping_postcode')
                            ->label('Irányítószám'),
                        TextInput::make('shipping_city')
                            ->label('Város'),
                        TextInput::make('shipping_address_1')
                            ->label('Utca, házszám')
                            ->columnSpanFull(),
                        TextInput::make('shipping_address_2')
                            ->label('Emelet, ajtó (opcionális)')
                            ->columnSpanFull(),
                        TextInput::make('shipping_country')
                            ->label('Ország')
                            ->default('Magyarország'),
                        TextInput::make('shipping_state')
                            ->label('Megye'),
                    ]),
            ])
            ->statePath('data');
    }

    public function updateProfile(): void
    {
        $data = $this->form->getState();

        $user = Auth::user();
        $user->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?: null,
            'billing_name' => $data['billing_name'] ?: null,
            'billing_company_name' => $data['billing_company_name'] ?: null,
            'billing_vat_number' => $data['billing_vat_number'] ?: null,
            'billing_company_office' => $data['billing_company_office'] ?: null,
            'billing_postcode' => $data['billing_postcode'] ?: null,
            'billing_city' => $data['billing_city'] ?: null,
            'billing_address_1' => $data['billing_address_1'] ?: null,
            'billing_address_2' => $data['billing_address_2'] ?: null,
            'billing_country' => $data['billing_country'] ?: null,
            'billing_state' => $data['billing_state'] ?: null,
            'shipping_name' => $data['shipping_name'] ?: null,
            'shipping_postcode' => $data['shipping_postcode'] ?: null,
            'shipping_city' => $data['shipping_city'] ?: null,
            'shipping_address_1' => $data['shipping_address_1'] ?: null,
            'shipping_address_2' => $data['shipping_address_2'] ?: null,
            'shipping_country' => $data['shipping_country'] ?: null,
            'shipping_state' => $data['shipping_state'] ?: null,
        ]);

        Notification::make()
            ->title('Profil frissítve')
            ->body('A profil adatai sikeresen mentésre kerültek.')
            ->success()
            ->send();
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.required' => 'A jelenlegi jelszó megadása kötelező.',
            'current_password.current_password' => 'A jelenlegi jelszó helytelen.',
            'new_password.required' => 'Az új jelszó megadása kötelező.',
            'new_password.confirmed' => 'A jelszavak nem egyeznek.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        Notification::make()
            ->title('Jelszó frissítve')
            ->body('A jelszavad sikeresen megváltozott.')
            ->success()
            ->send();
    }

    public function render(): View
    {
        return view('livewire.profile');
    }
}
