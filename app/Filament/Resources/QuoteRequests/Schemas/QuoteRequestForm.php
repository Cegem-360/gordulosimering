<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuoteRequests\Schemas;

use App\Enums\QuoteRequestStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Handling an ajánlatkérés: status, internal note and the contact details
 * in case they need correcting. The products stay as they were sent.
 */
final class QuoteRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ügyintézés')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('status')
                            ->label('Állapot')
                            ->options(QuoteRequestStatus::class)
                            ->required(),
                        DateTimePicker::make('handled_at')
                            ->label('Lezárva'),
                        Textarea::make('admin_note')
                            ->label('Belső megjegyzés')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Ajánlatkérő')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')->label('Név')->required()->maxLength(255),
                        TextInput::make('company')->label('Cégnév')->maxLength(255),
                        TextInput::make('email')->label('E-mail')->email()->required()->maxLength(255),
                        TextInput::make('phone')->label('Telefon')->tel()->required()->maxLength(50),
                        Textarea::make('message')->label('Üzenet')->rows(4)->columnSpanFull(),
                    ]),
            ]);
    }
}
