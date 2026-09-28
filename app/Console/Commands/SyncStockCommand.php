<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StockSyncer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('A termékek szabad készletének frissítése az Integra7 raktárkészletéből')]
#[Signature('app:sync-stock
    {--dry-run : Riport készítése írás nélkül}')]
final class SyncStockCommand extends Command
{
    public function handle(StockSyncer $syncer): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Próbafuttatás – az adatbázis nem módosul.');
        }

        $stats = $syncer->sync($dryRun);

        $this->table(['', 'Darab'], [
            ['Frissítve', $stats['updated']],
            ['Változatlan', $stats['unchanged']],
            ['Nincs az Integrában', $stats['missing']],
        ]);

        return self::SUCCESS;
    }
}
