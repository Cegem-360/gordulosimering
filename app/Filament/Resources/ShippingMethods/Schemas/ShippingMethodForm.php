<?php

declare(strict_types=1);

namespace App\Filament\Resources\ShippingMethods\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class ShippingMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('cost')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->suffix('Ft')
                    ->helperText('Bruttó díj. Ha vannak súlysávok, azok érvényesek helyette.'),
                Toggle::make('requires_parcel_point')
                    ->helperText('A pénztárban a vevő a GLS térképen választ csomagpontot vagy csomagautomatát.'),
                Repeater::make('rates')
                    ->helperText('Nettó díjak; a pénztár hozzáadja a 27% ÁFA-t. A legnagyobb súlyhatár feletti kosárnál a mód nem választható. Súly nélküli termék 0 kg-nak számít.')
                    ->columnSpanFull()
                    ->columns(3)
                    ->defaultItems(0)
                    ->reorderable(false)
                    ->schema([
                        TextInput::make('max_weight')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('kg'),
                        TextInput::make('bank_transfer')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('Ft + ÁFA'),
                        TextInput::make('cash_on_delivery')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('Ft + ÁFA'),
                    ]),
            ]);
    }
}
