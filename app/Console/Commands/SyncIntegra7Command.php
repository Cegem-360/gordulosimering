<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Integra7Syncer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('A termékek készletének, árának és ERP-adatainak frissítése az Integra7-ből')]
#[Signature('app:sync-integra7
    {--dry-run : Riport készítése írás nélkül}')]
final class SyncIntegra7Command extends Command
{
    public function handle(Integra7Syncer $syncer): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Próbafuttatás – az adatbázis nem módosul.');
        }

        if ($syncer->isUploadInProgress()) {
            $this->warn('Az ERP épp feltölti az Integra7-et, a szinkron kimarad.');

            return self::SUCCESS;
        }

        $stats = $syncer->sync($dryRun);

        $this->table(['', 'Darab'], [
            ['Frissítve', $stats['updated']],
            ['Változatlan', $stats['unchanged']],
            ['Nincs az Integrában', $stats['missing']],
            ['Új (rejtve létrehozva)', $stats['created']],
        ]);

        return self::SUCCESS;
    }
}
