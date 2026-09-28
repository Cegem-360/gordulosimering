<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsletterSubscribers\Pages;

use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Models\NewsletterSubscriber;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ListNewsletterSubscribers extends ListRecords
{
    protected static string $resource = NewsletterSubscriberResource::class;

    /**
     * Every address with its signup date, oldest first, for the mailing tool.
     */
    public static function csvDownload(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['email', 'feliratkozas'], escape: '');

            NewsletterSubscriber::query()->orderBy('id')->each(function (NewsletterSubscriber $subscriber) use ($output): void {
                fputcsv($output, [$subscriber->email, $subscriber->created_at?->format('Y-m-d H:i')], escape: '');
            });

            fclose($output);
        }, 'hirlevel-feliratkozok-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exportálás (CSV)')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn (): StreamedResponse => self::csvDownload()),
        ];
    }
}
