<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationRules\Pages;

use App\Filament\Resources\RecommendationRules\RecommendationRuleResource;
use App\Services\RecommendationRuleImporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Override;
use RuntimeException;

final class ListRecommendationRules extends ListRecords
{
    protected static string $resource = RecommendationRuleResource::class;

    /**
     * @param  array{file: TemporaryUploadedFile}  $data
     */
    public static function import(array $data): void
    {
        try {
            $result = resolve(RecommendationRuleImporter::class)->import($data['file']->getRealPath());
        } catch (RuntimeException $exception) {
            Notification::make()->title('Az Excel-fájl nem olvasható.')->body($exception->getMessage())->danger()->send();

            return;
        }

        $body = "Új ajánlás: {$result['created']}, már meglévő: {$result['skipped']}.";

        if ($result['unresolved'] !== []) {
            $body .= ' Nem található kategória ezekhez a sorokhoz: ' . implode('; ', $result['unresolved']);
        }

        Notification::make()
            ->title('Az importálás kész.')
            ->body($body)
            ->{$result['unresolved'] === [] ? 'success' : 'warning'}()
            ->persistent($result['unresolved'] !== [])
            ->send();
    }

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Excel importálása')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->modalDescription('Az ügyfél táblázata szerint: A–C oszlop, amit vásárolnak (kategória, alkategória; MINDENHEZ = minden termék), D–F oszlop, amit mellé kínálunk (kategória, alkategória, vagy egy cikkszám eleje, pl. TMAS). Az első sor a fejléc. A már meglévő ajánlások nem duplázódnak.')
                ->schema([
                    FileUpload::make('file')
                        ->label('Excel-fájl (.xlsx)')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->storeFiles(false),
                ])
                ->action(fn (array $data) => self::import($data)),
            CreateAction::make(),
        ];
    }
}
