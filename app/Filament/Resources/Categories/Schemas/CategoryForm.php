<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

final class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kategória')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Név')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, callable $set): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required(),
                        Select::make('category_id')
                            ->label('Szülő kategória')
                            ->relationship('parentCategory', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Nincs (főkategória)'),
                        TextInput::make('display')
                            ->label('Megjelenítés'),
                        TextInput::make('sort_order')
                            ->label('Sorrend')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Kisebb szám előrébb kerül a menüben és az alkategóriák között. Üresen a kategória-táblázat sorrendje érvényes.'),
                        FileUpload::make('image')
                            ->label('Kép')
                            ->helperText('Az alkategória-csempén jelenik meg. Kép nélkül az első termék képe látszik.')
                            ->image()
                            ->disk('public')
                            ->directory('categories')
                            ->imageEditor()
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label('Leírás')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
