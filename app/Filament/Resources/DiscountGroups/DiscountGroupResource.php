<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountGroups;

use App\Enums\NavigationGroup;
use App\Filament\Resources\DiscountGroups\Pages\CreateDiscountGroup;
use App\Filament\Resources\DiscountGroups\Pages\EditDiscountGroup;
use App\Filament\Resources\DiscountGroups\Pages\ListDiscountGroups;
use App\Filament\Resources\DiscountGroups\Schemas\DiscountGroupForm;
use App\Filament\Resources\DiscountGroups\Tables\DiscountGroupsTable;
use App\Models\DiscountGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use MadBox99\FilamentTranslatableModelLabels\Concerns\TranslatesFilamentModelLabels;
use Override;
use UnitEnum;

final class DiscountGroupResource extends Resource
{
    use TranslatesFilamentModelLabels;

    protected static ?string $model = DiscountGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Webshop;

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Kedvezménycsoportok';

    protected static ?string $recordTitleAttribute = 'code';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return DiscountGroupForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return DiscountGroupsTable::configure($table);
    }

    #[Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListDiscountGroups::route('/'),
            'create' => CreateDiscountGroup::route('/create'),
            'edit' => EditDiscountGroup::route('/{record}/edit'),
        ];
    }
}
