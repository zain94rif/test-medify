<?php

namespace App\Support;

use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

class MasterItemsExcelExporter
{
    private const HEADERS = [
        'No',
        'Nama kategori',
        'Nama items',
        'Nama supplier',
        'Harga',
        'Laba',
        'Harga jual',
    ];

    public function create(Collection $items): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP zip diperlukan untuk membuat file Excel.');
        }

        $path = tempnam(sys_get_temp_dir(), 'master-items-');
        if ($path === false) {
            throw new RuntimeException('File sementara Excel gagal dibuat.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            unlink($path);
            throw new RuntimeException('Workbook Excel gagal dibuka untuk ditulis.');
        }

        $completed = false;

        try {
            $files = [
                '[Content_Types].xml' => $this->contentTypesXml(),
                '_rels/.rels' => $this->packageRelationshipsXml(),
                'xl/workbook.xml' => $this->workbookXml(),
                'xl/_rels/workbook.xml.rels' => $this->workbookRelationshipsXml(),
                'xl/worksheets/sheet1.xml' => $this->worksheetXml($items),
            ];

            foreach ($files as $name => $contents) {
                if (!$zip->addFromString($name, $contents)) {
                    throw new RuntimeException("Workbook Excel gagal menulis {$name}.");
                }
            }

            if (!$zip->close()) {
                throw new RuntimeException('Workbook Excel gagal disimpan.');
            }

            $completed = true;
        } finally {
            if (!$completed) {
                $zip->close();
                if (is_file($path) && !unlink($path)) {
                    throw new RuntimeException('File sementara Excel gagal dibersihkan.');
                }
            }
        }

        return $path;
    }

    private function worksheetXml(Collection $items): string
    {
        $rows = [$this->rowXml(1, self::HEADERS)];

        foreach ($items->values() as $index => $item) {
            $price = (int) $item->harga_beli;
            $profit = (int) $item->laba;
            $salePrice = (int) round($price + ($price * $profit / 100));

            $rows[] = $this->rowXml($index + 2, [
                $index + 1,
                $item->categories->pluck('nama')->implode(', '),
                $item->nama,
                $item->supplier,
                $price,
                $profit,
                $salePrice,
            ]);
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . implode('', $rows) . '</sheetData></worksheet>';
    }

    private function rowXml(int $rowNumber, array $values): string
    {
        $cells = '';

        foreach ($values as $index => $value) {
            $reference = $this->columnName($index + 1) . $rowNumber;
            if (is_int($value) || is_float($value)) {
                $cells .= '<c r="' . $reference . '"><v>' . $value . '</v></c>';
                continue;
            }

            $escaped = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $preserveWhitespace = trim((string) $value) !== (string) $value
                ? ' xml:space="preserve"'
                : '';
            $cells .= '<c r="' . $reference . '" t="inlineStr"><is><t' . $preserveWhitespace . '>'
                . $escaped . '</t></is></c>';
        }

        return '<row r="' . $rowNumber . '">' . $cells . '</row>';
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)) . $name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>';
    }

    private function packageRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Master Items" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';
    }
}
