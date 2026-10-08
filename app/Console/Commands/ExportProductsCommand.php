<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductCsvExporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Description('Termékek exportálása CSV-be minden adatbázis-mezővel és a kategóriákkal')]
#[Signature('app:export-products
    {--path= : Egyedi kimeneti útvonal (alapértelmezés: storage/app/exports/termekek-<dátum>.csv)}
    {--only-web-visible : Csak a webáruházban szereplő termékek exportálása}')]
final class ExportProductsCommand extends Command
{
    public function handle(ProductCsvExporter $exporter): int
    {
        $path = $this->option('path') ?: storage_path('app/exports/termekek-' . now()->format('Y-m-d-His') . '.csv');

        File::ensureDirectoryExists(dirname($path));

        $handle = @fopen($path, 'wb');

        if ($handle === false) {
            $this->error('A fájl nem írható: ' . $path);

            return self::FAILURE;
        }

        $query = Product::query()->when($this->option('only-web-visible'), fn ($query) => $query->webVisible());

        $count = $exporter->write($handle, $query);
        fclose($handle);

        $this->info($count . ' termék exportálva: ' . $path);

        return self::SUCCESS;
    }
}
