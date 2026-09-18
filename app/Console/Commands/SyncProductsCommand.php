<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductSyncer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Termékek frissítése az ERP webshop-exportjából termékkód alapján, a webshopos adatok megtartásával')]
#[Signature('app:sync-products
    {--path= : Egyedi TSV útvonal (alapértelmezés: database/data/termekek.tsv)}
    {--only-web-visible : Csak a webáruházban szereplő termékek szinkronizálása}
    {--dry-run : Riport készítése írás nélkül}')]
final class SyncProductsCommand extends Command
{
    public function handle(ProductSyncer $syncer): int
    {
        $path = $this->option('path') ?: database_path('data/termekek.tsv');

        if (! file_exists($path)) {
            $this->error('TSV file not found: ' . $path);

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Próbafuttatás – az adatbázis nem módosul.');
        }

        $this->info('Termékek szinkronizálása: ' . $path);

        $stats = $syncer->sync($path, (bool) $this->option('only-web-visible'), $dryRun);

        $this->table(['', 'Darab'], [
            ['Létrehozva', $stats['created']],
            ['Frissítve', $stats['updated']],
            ['Változatlan', $stats['unchanged']],
            ['Deaktiválva', $stats['deactivated']],
            ['Kihagyva', $stats['skipped']],
        ]);

        return self::SUCCESS;
    }
}
