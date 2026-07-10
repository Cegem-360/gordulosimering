<?php

declare(strict_types=1);

use App\Models\Category;
use App\Services\CategoryImporter;

function writeTsv(string $content): string
{
    $path = tempnam(sys_get_temp_dir(), 'cat') . '.tsv';
    file_put_contents($path, $content);

    return $path;
}

it('builds a variable-depth tree of internal nodes and excludes leaf product lines', function (): void {
    // CRLF sorvégek; üres elválasztó sor a két ág között. A soronkénti legmélyebb
    // cella (levél) márka termék-sor, nem kategória.
    $rows = [
        "CSAPÁGYAK\tGOLYÓS\tMÉLYHORNYÚ\tEGYSORÚ\tBECO golyóscsapágy",
        "\t\t\t\tEZO golyóscsapágy",
        "\t\t\t\t",
        "CSAPÁGYAK\tGOLYÓS\tMÉLYHORNYÚ\tKÉTSORÚ\tFAG kétsorú",
        "ZSÍRZÁSTECHNIKA\tOKS zsír",
    ];
    $path = writeTsv(implode("\r\n", $rows) . "\r\n");

    $importer = new CategoryImporter();
    $importer->importTree($path);

    // Csak a belső csomópontok kategóriák.
    expect(Category::query()->pluck('name')->sort()->values()->all())
        ->toBe(['CSAPÁGYAK', 'EGYSORÚ', 'GOLYÓS', 'KÉTSORÚ', 'MÉLYHORNYÚ', 'ZSÍRZÁSTECHNIKA']);

    // Főkategóriák
    Category::query()->where('name', 'CSAPÁGYAK')->whereNull('category_id')->firstOrFail();
    Category::query()->where('name', 'ZSÍRZÁSTECHNIKA')->whereNull('category_id')->firstOrFail();

    // Öröklődés: EGYSORÚ és KÉTSORÚ is a MÉLYHORNYÚ alatt (a 2. sor col1-3 üres, örökli).
    $melyhornyu = Category::query()->where('name', 'MÉLYHORNYÚ')->firstOrFail();
    expect($melyhornyu->children->pluck('name')->sort()->values()->all())->toBe(['EGYSORÚ', 'KÉTSORÚ']);

    // A levelek (márka termék-sorok) NEM kategóriák.
    expect(Category::query()->whereIn('name', [
        'BECO golyóscsapágy', 'EZO golyóscsapágy', 'FAG kétsorú', 'OKS zsír',
    ])->count())->toBe(0);

    // Üres sor nem hozott létre kategóriát.
    expect(Category::query()->whereIn('name', ['', null])->count())->toBe(0);
});

it('generates unique slugs for same-named internal nodes in different branches', function (): void {
    $rows = [
        "A\tKÖZÖS\tx termék",
        "B\tKÖZÖS\ty termék",
    ];
    $path = writeTsv(implode("\r\n", $rows) . "\r\n");

    (new CategoryImporter())->importTree($path);

    $slugs = Category::query()->where('name', 'KÖZÖS')->pluck('slug');
    expect($slugs)->toHaveCount(2)
        ->and($slugs->unique())->toHaveCount(2);
});

it('is idempotent across repeated runs', function (): void {
    $rows = ["A\tB\tC"];
    $path = writeTsv(implode("\r\n", $rows) . "\r\n");

    $importer = new CategoryImporter();
    $importer->importTree($path);

    $first = Category::query()->count();
    $importer->importTree($path);

    expect(Category::query()->count())->toBe($first);
});
