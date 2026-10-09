<?php

declare(strict_types=1);

namespace App\Filament\Resources\Catalogs\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CatalogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Cím')
                            ->placeholder('pl. SKF általános csapágykatalógus')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Rövid leírás')
                            ->rows(2)
                            ->maxLength(500),
                        FileUpload::make('file')
                            ->label('PDF fájl')
                            ->required()
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(51200)
                            ->disk('public')
                            ->directory('catalogs')
                            ->downloadable(),
                        Toggle::make('is_active')
                            ->label('Látható a Katalógusok oldalon')
                            ->default(true),
                    ]),
            ]);
    }
}
