<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProductImageImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

#[Description('Termékképek importálása a termek_kepek.tsv-ből, termékkód (és csoportkód-wildcard) alapján')]
#[Signature('app:import-product-images {--path= : Egyedi TSV útvonal} {--sheet : A legfrissebb változat letöltése az ügyfél publikált táblázatából (shop.product_images_sheet_url)} {--max=25 : Egy kódhoz tartozó maximális termékszám (felette túl-generikus, kihagyva)} {--fresh : Előbb minden importált képet levesz a termékekről (az admin feltöltéseit megtartja)}')]
final class ImportProductImagesCommand extends Command
{
    public function handle(ProductImageImporter $importer): int
    {
        $path = $this->option('sheet') ? $this->downloadSheet() : ($this->option('path') ?: database_path('data/termek_kepek.tsv'));

        if ($path === null) {
            return self::FAILURE;
        }

        if (! file_exists($path)) {
            $this->error('TSV file not found: ' . $path);

            return self::FAILURE;
        }

        $this->info('Termékképek importálása...');
        $stats = $importer->import($path, (int) $this->option('max'), (bool) $this->option('fresh'));

        $this->table(['', 'Darab'], [
            ['Levett régi kép (--fresh)', $stats['cleared']],
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

    /**
     * Letölti a publikált táblázatot egy ideiglenes fájlba. Ha a letöltés nem
     * sikerül, vagy nem a várt táblázat jön vissza (pl. bejelentkező oldal),
     * null-t ad, így a --fresh sem vesz le egyetlen képet sem.
     */
    private function downloadSheet(): ?string
    {
        $url = (string) config('shop.product_images_sheet_url');

        try {
            $response = Http::timeout(60)->retry(2, 1000, throw: false)->get($url);
        } catch (Throwable $throwable) {
            $this->error('A táblázat letöltése nem sikerült: ' . $throwable->getMessage());

            return null;
        }

        $lines = array_values(array_filter(preg_split('/\r\n|\n|\r/', $response->body()) ?: [], fn (string $line): bool => mb_trim($line) !== ''));

        if (! $response->successful() || count($lines) < 2 || ! str_starts_with(mb_strtoupper($lines[0]), 'TERM') || ! str_contains($lines[0], "\t")) {
            $this->error(sprintf('A táblázat nem a várt formában jött le (HTTP %d, %d sor): %s', $response->status(), count($lines), $url));

            return null;
        }

        $path = storage_path('app/private/termek_kepek_sheet.tsv');
        file_put_contents($path, implode("\n", $lines) . "\n");

        $this->info(sprintf('Táblázat letöltve: %d sor', count($lines) - 1));

        return $path;
    }
}
