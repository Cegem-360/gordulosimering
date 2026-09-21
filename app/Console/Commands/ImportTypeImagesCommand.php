<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductTypeImageImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Típusképek hozzárendelése a termékekhez termékféleség alapján, a meglévő egyedi képek megtartásával')]
#[Signature('app:import-type-images
    {--path= : Egyedi TSV útvonal (alapértelmezés: database/data/termek_tipuskepek.tsv)}
    {--dry-run : Riport készítése írás nélkül}')]
final class ImportTypeImagesCommand extends Command
{
    public function handle(ProductTypeImageImporter $importer): int
    {
        $path = $this->option('path') ?: database_path('data/termek_tipuskepek.tsv');

        if (! file_exists($path)) {
            $this->error('TSV file not found: ' . $path);

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Próbafuttatás – az adatbázis nem módosul.');
        }

        $this->info('Típusképek importálása: ' . $path);

        $stats = $importer->import($path, $dryRun);

        $this->table(['', 'Darab'], [
            ['Termékféleség a fájlban', $stats['types']],
            ['Ebből talált terméket', $stats['matched_types']],
            ['Képet kapott termék', $stats['products']],
        ]);

        return self::SUCCESS;
    }
}
