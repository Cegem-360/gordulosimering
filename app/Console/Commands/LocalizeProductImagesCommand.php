<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductImageLocalizer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Külső termékképek letöltése a saját tárhelyre, URL-enként egyszer, és a termékmezők átírása helyi útvonalra')]
#[Signature('app:localize-product-images {--dry-run : Riport készítése letöltés és írás nélkül}')]
final class LocalizeProductImagesCommand extends Command
{
    public function handle(ProductImageLocalizer $localizer): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Próbafuttatás – nincs letöltés és az adatbázis sem módosul.');
        }

        $this->info('Külső termékképek letöltése...');

        $stats = $localizer->localize($dryRun);

        $this->table(['', 'Darab'], [
            ['Különböző külső kép', $stats['urls']],
            ['Letöltve', $stats['downloaded']],
            ['Sikertelen', $stats['failed']],
            ['Érintett termék', $stats['products']],
        ]);

        if ($stats['failed_urls'] !== []) {
            $this->warn('Sikertelen letöltések (az eredeti URL marad érvényben):');
            foreach (array_slice($stats['failed_urls'], 0, 20) as $url) {
                $this->line('  ' . $url);
            }
        }

        return self::SUCCESS;
    }
}
