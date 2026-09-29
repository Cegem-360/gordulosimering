<?php

declare(strict_types=1);

use App\Services\ProductAttributeExtractor;

it('reads the dimensions from the start of the size', function (?string $size, ?float $inner, ?float $outer, ?float $width): void {
    expect((new ProductAttributeExtractor())->extract(null, $size))
        ->inner_diameter->toBe($inner)
        ->outer_diameter->toBe($outer)
        ->width->toBe($width);
})->with([
    'three parts' => ['25X47X8', 25.0, 47.0, 8.0],
    'lower-case x' => ['20x47x14', 20.0, 47.0, 14.0],
    'decimal commas' => ['60,32X82,55X9,52', 60.32, 82.55, 9.52],
    'decimal points' => ['25.4X50.8X15', 25.4, 50.8, 15.0],
    'double width' => ['65X85X7/7,5', 65.0, 85.0, 7.0],
    'trailing word' => ['25X62X25 DOMBORÚ', 25.0, 62.0, 25.0],
    'trailing reference' => ['100X157X42/34 (VKHB 2032)', 100.0, 157.0, 42.0],
    'two parts keep only the bore' => ['18X1,2', 18.0, null, null],
    'unfinished third part' => ['85,725X136,525X', 85.725, null, null],
    'third part is not a number' => ['50X110X M24', 50.0, null, null],
    'free text' => ['A28,5', null, null, null],
    'code in front' => ['12655 10X300', null, null, null],
    'belt profile' => ['PHP 4SPA315TB', null, null, null],
    'empty' => ['', null, null, null],
    'null' => [null, null, null, null],
]);

it('takes the brand from the first word of the name', function (?string $name, ?string $brand): void {
    expect((new ProductAttributeExtractor())->extract($name, null)['brand'])->toBe($brand);
})->with([
    'SKF' => ['SKF egysorú mélyhornyú golyóscsapágy', 'SKF'],
    'SKF/Ewellix' => ['SKF/Ewellix lineáris vezeték', 'SKF'],
    'ZKL/ZVL' => ['ZKL/ZVL hengergörgős csapágy', 'ZKL'],
    'lower-case first word' => ['koyo kúpgörgős csapágy', 'KOYO'],
    'comma after the brand' => ['SEEGER, rögzítő gyűrű', 'SEEGER'],
    'KELETI is not a brand' => ['KELETI golyóscsapágy', null],
    'plain description' => ['Gumiházas simmering, NBR', null],
    'brand later in the name' => ['Csapágy SKF', null],
    'empty' => ['', null],
    'null' => [null, null],
]);

it('finds the material in the name', function (?string $name, ?string $material): void {
    expect((new ProductAttributeExtractor())->extract($name, null)['material'])->toBe($material);
})->with([
    'NBR' => ['Gumiházas simmering, NBR', 'NBR'],
    'VITON' => ['Gumiházas simmering, VITON', 'FKM (Viton)'],
    'FKM' => ['O-gyűrű FKM 80', 'FKM (Viton)'],
    'EPDM' => ['O-gyűrű EPDM', 'EPDM'],
    'PTFE' => ['PTFE tömítőgyűrű', 'PTFE'],
    'stainless' => ['SEEGER rögzítő gyűrű DIN471 rozsdamentes', 'Rozsdamentes'],
    'stainless capitalised' => ['Rozsdamentes golyóscsapágy', 'Rozsdamentes'],
    'only whole words' => ['HNBRX tömítés', null],
    'first in the list wins' => ['Simmering NBR/VITON', 'NBR'],
    'none' => ['SKF golyóscsapágy', null],
    'null' => [null, null],
]);
