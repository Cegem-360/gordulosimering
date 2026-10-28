<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

/**
 * Reads the cell texts of an .xlsx file's first worksheet, enough for the
 * client's simple lists; no formulas, styles or dates.
 */
final class XlsxReader
{
    private const string MAIN_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const string REL_NS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    /**
     * The rows of the first worksheet, each a list of cell texts from column A
     * on ('' for empty cells). Empty rows are left out.
     *
     * @return array<int, array<int, string>>
     */
    public function rows(string $path): array
    {
        $zip = new ZipArchive();

        throw_if($zip->open($path) !== true, RuntimeException::class, 'Az Excel-fájl nem nyitható meg.');

        try {
            $sharedStrings = $this->sharedStrings($zip);
            $sheet = $this->xml($zip, $this->firstSheetPath($zip));
        } finally {
            $zip->close();
        }

        $rows = [];

        foreach ($sheet->children(self::MAIN_NS)->sheetData->row as $row) {
            $cells = [];

            foreach ($row->children(self::MAIN_NS)->c as $cell) {
                $cells[$this->columnIndex((string) $cell->attributes()['r'])] = $this->cellText($cell, $sharedStrings);
            }

            if (array_filter($cells, fn (string $text): bool => $text !== '') === []) {
                continue;
            }

            $row = array_fill(0, max(array_keys($cells)) + 1, '');
            $rows[] = array_replace($row, $cells);
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private function sharedStrings(ZipArchive $zip): array
    {
        if ($zip->locateName('xl/sharedStrings.xml') === false) {
            return [];
        }

        $strings = [];

        foreach ($this->xml($zip, 'xl/sharedStrings.xml')->children(self::MAIN_NS)->si as $item) {
            $strings[] = $this->text($item);
        }

        return $strings;
    }

    private function firstSheetPath(ZipArchive $zip): string
    {
        $workbook = $this->xml($zip, 'xl/workbook.xml');
        $relationId = (string) $workbook->children(self::MAIN_NS)->sheets->sheet[0]->attributes(self::REL_NS)['id'];

        foreach ($this->xml($zip, 'xl/_rels/workbook.xml.rels')->children() as $relation) {
            if ((string) $relation->attributes()['Id'] === $relationId) {
                $target = (string) $relation->attributes()['Target'];

                return str_starts_with($target, '/') ? mb_ltrim($target, '/') : 'xl/' . $target;
            }
        }

        throw new RuntimeException('Az Excel-fájlban nincs munkalap.');
    }

    /**
     * @param  array<int, string>  $sharedStrings
     */
    private function cellText(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $value = $cell->children(self::MAIN_NS);

        $text = match ((string) $cell->attributes()['t']) {
            's' => $sharedStrings[(int) $value->v] ?? '',
            'inlineStr' => $this->text($value->is),
            default => (string) $value->v,
        };

        return mb_trim($text);
    }

    /**
     * The text of a string item, joining rich-text runs.
     */
    private function text(SimpleXMLElement $item): string
    {
        $item->registerXPathNamespace('m', self::MAIN_NS);

        return implode('', array_map(strval(...), $item->xpath('.//m:t') ?: []));
    }

    private function columnIndex(string $reference): int
    {
        $letters = preg_replace('/\d+/', '', $reference) ?? '';
        $index = 0;

        foreach (mb_str_split(mb_strtoupper($letters)) as $letter) {
            $index = $index * 26 + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function xml(ZipArchive $zip, string $name): SimpleXMLElement
    {
        $content = $zip->getFromName($name);

        throw_if($content === false, RuntimeException::class, "Hiányzó rész az Excel-fájlban: {$name}");

        return new SimpleXMLElement($content, LIBXML_NONET);
    }
}
