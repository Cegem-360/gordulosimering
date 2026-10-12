<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Filament\Actions\ImportAction;
use League\Csv\Reader;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Override;

/**
 * Az ImportAction a tabulátort felismeri elválasztóként, de `filled()`-del
 * ellenőrzi, ami a tabulátort üresnek veszi, így vesszővel olvasna
 * (filament/actions v5.10.1). A tabulátoros fájlt ezért beolvasás előtt
 * pontosvesszősre alakítjuk; minden olvasás ezen a metóduson megy át.
 */
final class TabAwareImportAction extends ImportAction
{
    /**
     * @return resource|false
     */
    #[Override]
    public function getUploadedFileStream(TemporaryUploadedFile $file)
    {
        $stream = parent::getUploadedFileStream($file);

        if (! is_resource($stream)) {
            return $stream;
        }

        rewind($stream);
        $content = (string) stream_get_contents($stream);
        fclose($stream);

        $converted = fopen('php://temp', 'w+b');

        if (! $this->isTabSeparated($content)) {
            fwrite($converted, $content);
        } else {
            foreach (Reader::fromString($content)->setDelimiter("\t")->setEscape('')->getRecords() as $record) {
                fputcsv($converted, $record, ';', escape: '');
            }
        }

        rewind($converted);

        return $converted;
    }

    private function isTabSeparated(string $content): bool
    {
        $firstLine = strtok($content, "\n") ?: '';

        return mb_substr_count($firstLine, "\t") > max(mb_substr_count($firstLine, ';'), mb_substr_count($firstLine, ','));
    }
}
