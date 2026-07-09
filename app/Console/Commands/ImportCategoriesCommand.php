<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CategoryImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Kategóriafa importálása a web_kategoriak.tsv-ből, opcionális termék-összekötéssel')]
#[Signature('app:import-categories {--link : Termékek hozzákötése a kategórialevelekhez}')]
final class ImportCategoriesCommand extends Command
{
    public function handle(CategoryImporter $importer): int
    {
        $path = database_path('data/web_kategoriak.tsv');

        if (! file_exists($path)) {
            $this->error('TSV file not found: ' . $path);

            return self::FAILURE;
        }

        $this->info('Kategóriafa importálása...');
        $count = $importer->importTree($path);
        $this->info(sprintf('Kész: %d kategória feldolgozva.', $count));

        if ($this->option('link')) {
            $this->info('Termékek összekötése a kategóriákkal...');
            $links = $importer->linkProducts();
            $this->info(sprintf('Kész: %d termék-kapcsolat létrehozva.', $links));
        }

        return self::SUCCESS;
    }
}
