<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Enums\CustomerType;
use App\Models\User;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

/**
 * The customer details the checkout and the Ajánlatkérés page both ask for:
 * private person or company first, and only then the name, contact, company
 * details and billing address, so nobody goes on by mistake. The state lives
 * in $data under the billing_* keys.
 */
trait HasCustomerDetailsForm
{
    /**
     * Whether the customer picked private person or company; the rest of
     * the form shows only after that.
     */
    public function hasChosenCustomerType(): bool
    {
        return filled($this->data['customer_type'] ?? null);
    }

    /**
     * The saved details of a logged-in customer, to fill the form with.
     *
     * @return array<string, mixed>
     */
    protected function customerDetailsOf(User $user): array
    {
        return [
            'customer_type' => $user->customer_type,
            'billing_name' => $user->billing_name ?? $user->name,
            'billing_email' => $user->email,
            'billing_phone' => $user->phone ?? '',
            'billing_company_name' => $user->billing_company_name ?? '',
            'billing_vat_number' => $user->billing_vat_number ?? '',
            'billing_postcode' => $user->billing_postcode ?? '',
            'billing_city' => $user->billing_city ?? '',
            'billing_address_1' => $user->billing_address_1 ?? '',
            'billing_address_2' => $user->billing_address_2 ?? '',
            'billing_country' => $user->billing_country ?? 'Magyarország',
            'billing_state' => $user->billing_state ?? '',
        ];
    }

    /**
     * @param  string  $heading  the first section's, "Vásárló" or "Ajánlatkérő"
     * @param  string  $action  what the customer does as a private person or company, "vásárol"
     * @return array<int, Section>
     */
    protected function customerDetailsSections(string $heading, string $action): array
    {
        return [
            Section::make($heading)
                ->description("Kérjük, először válassza ki, hogy magánszemélyként vagy cégként {$action}.")
                ->columnSpanFull()
                ->schema([
                    Radio::make('customer_type')
                        ->hiddenLabel()
                        ->options(CustomerType::class)
                        ->enum(CustomerType::class)
                        ->required()
                        ->validationMessages(['required' => "Kérjük, válassza ki, hogy magánszemélyként vagy cégként {$action}."])
                        ->inline()
                        ->live(),
                ]),
            Section::make('Számlázási adatok')
                ->visible(fn (): bool => $this->hasChosenCustomerType())
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('billing_name')
                        ->label('Név')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('billing_email')
                        ->label('Email cím')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('billing_phone')
                        ->label('Telefonszám')
                        ->tel()
                        ->required()
                        ->maxLength(50),
                    TextInput::make('billing_company_name')
                        ->label('Cégnév')
                        ->visible(fn (Get $get): bool => $get->enum('customer_type', CustomerType::class) === CustomerType::Company)
                        ->required()
                        ->maxLength(255),
                    TextInput::make('billing_vat_number')
                        ->label('Adószám')
                        ->visible(fn (Get $get): bool => $get->enum('customer_type', CustomerType::class) === CustomerType::Company)
                        ->required()
                        ->maxLength(255),
                ]),
            Section::make('Számlázási cím')
                ->visible(fn (): bool => $this->hasChosenCustomerType())
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('billing_postcode')
                        ->label('Irányítószám')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('billing_city')
                        ->label('Város')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('billing_address_1')
                        ->label('Utca, házszám')
                        ->columnSpanFull()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('billing_address_2')
                        ->label('Emelet, ajtó (opcionális)')
                        ->columnSpanFull()
                        ->maxLength(255),
                    TextInput::make('billing_country')
                        ->label('Ország')
                        ->default('Magyarország')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('billing_state')
                        ->label('Megye')
                        ->maxLength(255),
                ]),
        ];
    }
}
