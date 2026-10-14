<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationRules\Schemas;

use App\Filament\Resources\RecommendationRules\RecommendationRuleResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class RecommendationRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ezt vásárolják')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('source_category_id')
                            ->label('Kategória')
                            ->options(fn (): array => RecommendationRuleResource::categoryPaths())
                            ->searchable()
                            ->placeholder('Minden termék (MINDENHEZ)')
                            ->helperText('Az alkategóriáival együtt. Üresen hagyva minden termékhez ajánl.'),
                    ]),
                Section::make('Ezt kínáljuk mellé')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('target_category_id')
                            ->label('Kategória')
                            ->options(fn (): array => RecommendationRuleResource::categoryPaths())
                            ->searchable()
                            ->requiredWithout('target_code_prefix')
                            ->prohibits('target_code_prefix')
                            ->helperText('Az alkategóriáival együtt.'),
                        TextInput::make('target_code_prefix')
                            ->label('vagy a cikkszám eleje')
                            ->placeholder('pl. TMAS')
                            ->maxLength(100)
                            ->helperText('Minden termék, amelynek a cikkszáma így kezdődik.'),
                    ]),
                Toggle::make('is_active')
                    ->label('Aktív')
                    ->default(true),
            ]);
    }
}
