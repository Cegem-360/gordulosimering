<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterSubscribers;

use App\Enums\NavigationGroup;
use App\Filament\Resources\NewsletterSubscribers\Pages\ListNewsletterSubscribers;
use App\Filament\Resources\NewsletterSubscribers\Tables\NewsletterSubscribersTable;
use App\Models\NewsletterSubscriber;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

/**
 * The addresses from the footer signup. There is no create or edit page:
 * addresses only arrive through the form, and an unsubscribe request sent
 * to gs@ is handled by deleting the row.
 */
final class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Marketing;

    protected static ?string $navigationLabel = 'Hírlevél-feliratkozók';

    protected static ?string $modelLabel = 'feliratkozó';

    protected static ?string $pluralModelLabel = 'hírlevél-feliratkozók';

    protected static ?string $recordTitleAttribute = 'email';

    #[Override]
    public static function table(Table $table): Table
    {
        return NewsletterSubscribersTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListNewsletterSubscribers::route('/'),
        ];
    }
}
