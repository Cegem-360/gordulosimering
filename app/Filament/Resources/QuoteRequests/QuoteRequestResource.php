<?php

declare(strict_types=1);

namespace App\Filament\Resources\QuoteRequests;

use App\Enums\NavigationGroup;
use App\Filament\Resources\QuoteRequests\Pages\EditQuoteRequest;
use App\Filament\Resources\QuoteRequests\Pages\ListQuoteRequests;
use App\Filament\Resources\QuoteRequests\Pages\ViewQuoteRequest;
use App\Filament\Resources\QuoteRequests\Schemas\QuoteRequestForm;
use App\Filament\Resources\QuoteRequests\Schemas\QuoteRequestInfolist;
use App\Filament\Resources\QuoteRequests\Tables\QuoteRequestsTable;
use App\Models\QuoteRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

/**
 * The ajánlatkérések sent from the storefront. There is no create page:
 * requests only arrive through the Ajánlatkérés form.
 */
final class QuoteRequestResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Webshop;

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Ajánlatkérések';

    protected static ?string $modelLabel = 'ajánlatkérés';

    protected static ?string $pluralModelLabel = 'Ajánlatkérések';

    protected static ?string $recordTitleAttribute = 'reference';

    /**
     * How many requests still wait for an answer.
     */
    #[Override]
    public static function getNavigationBadge(): ?string
    {
        $open = QuoteRequest::query()->open()->count();

        return $open > 0 ? (string) $open : null;
    }

    #[Override]
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return QuoteRequestInfolist::configure($schema);
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return QuoteRequestForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return QuoteRequestsTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListQuoteRequests::route('/'),
            'view' => ViewQuoteRequest::route('/{record}'),
            'edit' => EditQuoteRequest::route('/{record}/edit'),
        ];
    }
}
