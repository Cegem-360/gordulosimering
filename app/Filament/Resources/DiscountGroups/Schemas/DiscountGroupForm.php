<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountGroups\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class DiscountGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->helperText('A termékek ERP-s „Csoportkód”-ja, pontosan úgy, ahogy a termékeken szerepel (pl. CT).')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                TextInput::make('name'),
            ]);
    }
}
