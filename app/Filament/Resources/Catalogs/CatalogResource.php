<?php

declare(strict_types=1);

namespace App\Filament\Resources\Catalogs;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Catalogs\Pages\CreateCatalog;
use App\Filament\Resources\Catalogs\Pages\EditCatalog;
use App\Filament\Resources\Catalogs\Pages\ListCatalogs;
use App\Filament\Resources\Catalogs\Schemas\CatalogForm;
use App\Filament\Resources\Catalogs\Tables\CatalogsTable;
use App\Models\Catalog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

/**
 * The PDFs on the storefront's Katalógusok page.
 */
final class CatalogResource extends Resource
{
    protected static ?string $model = Catalog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Content;

    protected static ?string $navigationLabel = 'Katalógusok';

    protected static ?string $modelLabel = 'katalógus';

    protected static ?string $pluralModelLabel = 'Katalógusok';

    protected static ?string $recordTitleAttribute = 'title';

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return CatalogForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return CatalogsTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListCatalogs::route('/'),
            'create' => CreateCatalog::route('/create'),
            'edit' => EditCatalog::route('/{record}/edit'),
        ];
    }
}
