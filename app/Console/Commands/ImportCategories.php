<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CategoryImporter;
use Illuminate\Console\Command;

final class ImportCategories extends Command
{
    protected $signature = 'app:import-categories {--link : Termékek hozzákötése a kategórialevelekhez}';

    protected $description = 'Kategóriafa importálása a web_kategoriak.tsv-ből, opcionális termék-összekötéssel';

    public function handle(CategoryImporter $importer): int
    {
        $path = database_path('data/web_kategoriak.tsv');

        if (! file_exists($path)) {
            $this->error("TSV file not found: {$path}");

            return self::FAILURE;
        }

        $this->info('Kategóriafa importálása...');
        $count = $importer->importTree($path);
        $this->info("Kész: {$count} kategória feldolgozva.");

        if ($this->option('link')) {
            $this->info('Termékek összekötése a kategóriákkal...');
            $links = $importer->linkProducts();
            $this->info("Kész: {$links} termék-kapcsolat létrehozva.");
        }

        return self::SUCCESS;
    }
}
