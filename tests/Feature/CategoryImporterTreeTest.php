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

it('builds a variable-depth tree with inheritance and skips blank rows', function () {
    // CRLF sorvégek; üres elválasztó sor a két ág között.
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

    // Főkategóriák
    $csapagyak = Category::where('name', 'CSAPÁGYAK')->whereNull('category_id')->firstOrFail();
    $zsir = Category::where('name', 'ZSÍRZÁSTECHNIKA')->whereNull('category_id')->firstOrFail();

    // Mélység és öröklődés: két levél a MÉLYHORNYÚ > EGYSORÚ alatt
    $egysoru = Category::where('name', 'EGYSORÚ')->firstOrFail();
    expect($egysoru->children->pluck('name')->sort()->values()->all())
        ->toBe(['BECO golyóscsapágy', 'EZO golyóscsapágy']);

    // Új érték a 4. oszlopban (KÉTSORÚ) nem keveredik az EGYSORÚ ággal
    $ketsoru = Category::where('name', 'KÉTSORÚ')->firstOrFail();
    expect($ketsoru->children->pluck('name')->all())->toBe(['FAG kétsorú']);

    // Depth-2 ág: ZSÍRZÁSTECHNIKA > OKS zsír
    expect(Category::where('name', 'OKS zsír')->first()->parentCategory->id)->toBe($zsir->id);

    // Üres sor nem hozott létre kategóriát
    expect(Category::whereIn('name', ['', null])->count())->toBe(0);
});

it('generates unique slugs for same-named nodes in different branches', function () {
    $rows = [
        "A\tKÖZÖS",
        "B\tKÖZÖS",
    ];
    $path = writeTsv(implode("\r\n", $rows) . "\r\n");

    (new CategoryImporter())->importTree($path);

    $slugs = Category::where('name', 'KÖZÖS')->pluck('slug');
    expect($slugs)->toHaveCount(2)
        ->and($slugs->unique())->toHaveCount(2);
});

it('is idempotent across repeated runs', function () {
    $rows = ["A\tB\tC"];
    $path = writeTsv(implode("\r\n", $rows) . "\r\n");

    $importer = new CategoryImporter();
    $importer->importTree($path);
    $first = Category::count();
    $importer->importTree($path);

    expect(Category::count())->toBe($first);
});
