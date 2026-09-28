<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\DiscountGroup;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Group::make()
                            ->columnSpan(2)
                            ->schema([
                                self::accountSection(),
                                self::billingSection(),
                                self::shippingSection(),
                            ]),
                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                self::accessSection(),
                                self::baseDiscountSection(),
                            ]),
                    ]),
                self::groupDiscountSection(),
            ]);
    }

    private static function accountSection(): Section
    {
        return Section::make('Fiók')
            ->columns(2)
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText(fn (string $operation): ?string => $operation === 'edit'
                        ? 'Csak akkor töltsd ki, ha új jelszót adsz.'
                        : null)
                    ->columnSpanFull(),
            ]);
    }

    private static function billingSection(): Section
    {
        return Section::make('Számlázási adatok')
            ->columns(2)
            ->schema([
                TextInput::make('billing_name'),
                TextInput::make('billing_company_name'),
                TextInput::make('billing_vat_number'),
                TextInput::make('billing_company_office'),
                TextInput::make('billing_postcode'),
                TextInput::make('billing_city'),
                TextInput::make('billing_address_1')
                    ->columnSpanFull(),
                TextInput::make('billing_address_2')
                    ->columnSpanFull(),
                TextInput::make('billing_country'),
                TextInput::make('billing_state'),
            ]);
    }

    private static function shippingSection(): Section
    {
        return Section::make('Szállítási cím')
            ->description('Ha eltér a számlázási címtől.')
            ->columns(2)
            ->collapsed()
            ->schema([
                TextInput::make('shipping_name')
                    ->columnSpanFull(),
                TextInput::make('shipping_postcode'),
                TextInput::make('shipping_city'),
                TextInput::make('shipping_address_1')
                    ->columnSpanFull(),
                TextInput::make('shipping_address_2')
                    ->columnSpanFull(),
                TextInput::make('shipping_country'),
                TextInput::make('shipping_state'),
            ]);
    }

    private static function accessSection(): Section
    {
        return Section::make('Hozzáférés')
            ->schema([
                Toggle::make('is_admin')
                    ->helperText('Beléphet az admin felületre.')
                    ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false),
                DateTimePicker::make('email_verified_at'),
            ]);
    }

    private static function baseDiscountSection(): Section
    {
        return Section::make('Alap kedvezmény')
            ->description('A vevő minden terméknél a legnagyobb kedvezményt kapja: az alap kedvezményt, a termék csoportkódjára adottat vagy az akciót. Ezek nem adódnak össze.')
            ->schema([
                self::percentageInput('base_discount_percentage')
                    ->default(10)
                    ->required()
                    ->helperText('Minden termékre jár.'),
            ]);
    }

    private static function groupDiscountSection(): Section
    {
        return Section::make('Csoportkedvezmények')
            ->description('Minden termékcsoport (Csoportkód) itt szerepel. Írd be a kedvezmény százalékát; az üresen hagyott vagy 0 csoportokra nincs kedvezmény. A lista a csoportokból épül, így új csoport magától megjelenik, a megszűnt eltűnik.')
            ->collapsible()
            ->columnSpanFull()
            ->columns([
                'default' => 2,
                'sm' => 3,
                'md' => 4,
                'xl' => 6,
            ])
            ->schema(self::groupDiscountInputs());
    }

    /**
     * @return array<int, TextInput>
     */
    private static function groupDiscountInputs(): array
    {
        return DiscountGroup::query()
            ->orderBy('code')
            ->get()
            ->map(fn (DiscountGroup $group): TextInput => self::percentageInput('group_discounts.' . $group->code)
                ->label($group->code)
                ->helperText(filled($group->name) ? $group->name : null))
            ->all();
    }

    private static function percentageInput(string $name): TextInput
    {
        return TextInput::make($name)
            ->numeric()
            ->minValue(0)
            ->maxValue(100)
            ->suffix('%');
    }
}
