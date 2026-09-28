<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\DiscountGroup;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Toggle::make('is_admin')
                    ->helperText('Beléphet az admin felületre.')
                    ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('billing_name'),
                TextInput::make('billing_company_name'),
                TextInput::make('billing_vat_number'),
                TextInput::make('billing_company_office'),
                TextInput::make('billing_postcode'),
                TextInput::make('billing_city'),
                TextInput::make('billing_address_1'),
                TextInput::make('billing_address_2'),
                TextInput::make('billing_country'),
                TextInput::make('billing_state'),
                TextInput::make('shipping_name'),
                TextInput::make('shipping_postcode'),
                TextInput::make('shipping_city'),
                TextInput::make('shipping_address_1'),
                TextInput::make('shipping_address_2'),
                TextInput::make('shipping_country'),
                TextInput::make('shipping_state'),
                Section::make('Kedvezmények')
                    ->description('A vevő minden terméknél a legnagyobb kedvezményt kapja: az alap kedvezményt, a termék csoportkódjára adottat vagy az akciót. Ezek nem adódnak össze.')
                    ->columnSpanFull()
                    ->schema([
                        self::percentageInput('base_discount_percentage')
                            ->default(10)
                            ->helperText('Minden termékre jár.'),
                        Repeater::make('discounts')
                            ->relationship()
                            ->schema([
                                Select::make('discount_group_id')
                                    ->relationship('discountGroup', 'code')
                                    ->getOptionLabelFromRecordUsing(fn (DiscountGroup $record): string => filled($record->name)
                                        ? $record->code . ' – ' . $record->name
                                        : $record->code)
                                    ->searchable(['code', 'name'])
                                    ->preload()
                                    ->distinct()
                                    ->required(),
                                self::percentageInput('percentage'),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Csoportkedvezmény hozzáadása'),
                    ]),
            ]);
    }

    private static function percentageInput(string $name): TextInput
    {
        return TextInput::make($name)
            ->numeric()
            ->minValue(0)
            ->maxValue(100)
            ->suffix('%')
            ->required();
    }
}
