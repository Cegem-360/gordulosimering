<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductImageImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Termékképek importálása a termek_kepek.tsv-ből, termékkód (és csoportkód-wildcard) alapján')]
#[Signature('app:import-product-images {--path= : Egyedi TSV útvonal} {--max=25 : Egy kódhoz tartozó maximális termékszám (felette túl-generikus, kihagyva)}')]
final class ImportProductImagesCommand extends Command
{
    public function handle(ProductImageImporter $importer): int
    {
        $path = $this->option('path') ?: database_path('data/termek_kepek.tsv');

        if (! file_exists($path)) {
            $this->error('TSV file not found: ' . $path);

            return self::FAILURE;
        }

        $this->info('Termékképek importálása...');
        $stats = $importer->import($path, (int) $this->option('max'));

        $this->table(['', 'Darab'], [
            ['Kódok képpel', $stats['codes']],
            ['Pontos egyezés', $stats['exact']],
            ['Wildcard/előtag egyezés', $stats['wildcard']],
            ['Nincs katalógusban', $stats['zero']],
            ['Túl-generikus (kihagyva)', $stats['skipped']],
            ['Képet kapott termék', $stats['products']],
        ]);

        if ($stats['skipped_codes'] !== []) {
            $this->warn('Kihagyott, túl-generikus kódok (kód => termékszám):');
            foreach (array_slice($stats['skipped_codes'], 0, 15, true) as $code => $count) {
                $this->line(sprintf('  %s => %d', $code, $count));
            }
        }

        return self::SUCCESS;
    }
}
