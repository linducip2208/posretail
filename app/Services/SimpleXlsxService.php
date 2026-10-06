<?php

namespace App\Services;

use ZipArchive;

/**
 * Writer XLSX minimal tanpa dependensi tambahan.
 * Memakai ZipArchive + XML bawaan PHP sehingga bisa
 * menghasilkan file .xlsx valid yang dibuka di Excel/LibreOffice.
 *
 * FIX: urutan elemen worksheet diperbaiki (dimension, sheetViews
 * WAJIB sebelum sheetData), target rels styles diperbaiki,
 * dan semua string disanitasi dari karakter kontrol ilegal XML.
 */
class SimpleXlsxService
{
    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, string>  $numericColumns  Huruf kolom yang diperlakukan sebagai angka (mis. ['H','I','J','K']).
     */
    public static function download(string $filename, array $headers, array $rows, array $numericColumns = []): mixed
    {
        return self::downloadMulti($filename, [
            ['name' => 'Laporan', 'headers' => $headers, 'rows' => $rows, 'numericColumns' => $numericColumns],
        ]);
    }

    /**
     * Download workbook multi-sheet.
     * @param  array<int, array{name:string, headers:array, rows:array, numericColumns?:array}>  $sheets
     */
    public static function downloadMulti(string $filename, array $sheets): mixed
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'xlsx').'.xlsx';
        @unlink($tmpPath);

        $zip = new ZipArchive;
        if ($zip->open($tmpPath, ZipArchive::CREATE) !== true) {
            abort(500, 'Gagal membuat file Excel.');
        }

        $normalized = [];
        foreach ($sheets as $i => $s) {
            $normalized[] = [
                'name' => self::sheetName($s['name'] ?? ('Sheet'.($i + 1))),
                'headers' => array_values($s['headers'] ?? []),
                'rows' => array_values($s['rows'] ?? []),
                'numericColumns' => $s['numericColumns'] ?? [],
            ];
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypes(count($normalized)));
        $zip->addFromString('_rels/.rels', self::rels());
        $zip->addFromString('xl/workbook.xml', self::workbook($normalized));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels(count($normalized)));
        $zip->addFromString('xl/styles.xml', self::styles());
        foreach ($normalized as $i => $s) {
            $zip->addFromString(
                'xl/worksheets/sheet'.($i + 1).'.xml',
                self::sheet($s['headers'], $s['rows'], $s['numericColumns'])
            );
        }
        $zip->close();

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /** Build file lalu kembalikan path (untuk email/penjadwalan). */
    public static function buildMulti(string $filename, array $sheets): string
    {
        $tmpPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$filename;
        @unlink($tmpPath);

        $zip = new ZipArchive;
        if ($zip->open($tmpPath, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Gagal membuat file Excel.');
        }

        $normalized = [];
        foreach ($sheets as $i => $s) {
            $normalized[] = [
                'name' => self::sheetName($s['name'] ?? ('Sheet'.($i + 1))),
                'headers' => array_values($s['headers'] ?? []),
                'rows' => array_values($s['rows'] ?? []),
                'numericColumns' => $s['numericColumns'] ?? [],
            ];
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypes(count($normalized)));
        $zip->addFromString('_rels/.rels', self::rels());
        $zip->addFromString('xl/workbook.xml', self::workbook($normalized));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels(count($normalized)));
        $zip->addFromString('xl/styles.xml', self::styles());
        foreach ($normalized as $i => $s) {
            $zip->addFromString(
                'xl/worksheets/sheet'.($i + 1).'.xml',
                self::sheet($s['headers'], $s['rows'], $s['numericColumns'])
            );
        }
        $zip->close();

        return $tmpPath;
    }

    protected static function sheetName(string $name): string
    {
        $name = trim($name) === '' ? 'Laporan' : trim($name);
        // Excel: maks 31 char, tanpa [ ] : * ? / \
        $name = preg_replace('/[\[\]\:\*\/\\\\\?]/u', ' ', $name);
        $name = mb_substr($name, 0, 31);

        return $name === '' ? 'Laporan' : $name;
    }

    protected static function colLetter(int $index): string
    {
        $letter = '';
        $index++;
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod).$letter;
            $index = (int) (($index - $mod - 1) / 26);
        }

        return $letter;
    }

    protected static function sanitize(string $value): string
    {
        // Buang karakter kontrol ilegal XML 1.0 + pastikan UTF-8 valid.
        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        return (string) preg_replace(
            '/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u',
            '',
            $value
        );
    }

    protected static function esc(string $value): string
    {
        return htmlspecialchars(self::sanitize($value), ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    protected static function sheet(array $headers, array $rows, array $numericColumns): string
    {
        $colCount = max(count($headers), 1);
        $lastCol = self::colLetter($colCount - 1);
        $rowNum = 1;
        $totalRows = count($rows) + 1;

        // URUTAN SESUAI SPEC ECMA-376: dimension -> sheetViews -> sheetFormatPr -> sheetData -> autoFilter
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<dimension ref="A1:'.$lastCol.$totalRows.'"/>';
        $xml .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
        $xml .= '<sheetFormatPr defaultRowHeight="15"/>';
        $xml .= '<sheetData>';

        $xml .= '<row r="'.$rowNum.'">';
        foreach ($headers as $i => $h) {
            $col = self::colLetter($i);
            $xml .= '<c r="'.$col.$rowNum.'" t="inlineStr" s="1"><is><t>'.self::esc((string) $h).'</t></is></c>';
        }
        $xml .= '</row>';

        foreach ($rows as $row) {
            $rowNum++;
            $xml .= '<row r="'.$rowNum.'">';
            foreach (array_values($row) as $i => $val) {
                $col = self::colLetter($i);
                if (in_array($col, $numericColumns, true) && is_numeric($val)) {
                    // Paksa format angka invariant (titik desimal).
                    $num = (string) (0 + $val);
                    $xml .= '<c r="'.$col.$rowNum.'"><v>'.$num.'</v></c>';
                } else {
                    $xml .= '<c r="'.$col.$rowNum.'" t="inlineStr"><is><t>'.self::esc((string) ($val ?? '')).'</t></is></c>';
                }
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData>';
        $xml .= '<autoFilter ref="A1:'.$lastCol.$rowNum.'"/>';
        $xml .= '</worksheet>';

        return $xml;
    }

    protected static function contentTypes(int $sheetCount): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        for ($i = 1; $i <= max($sheetCount, 1); $i++) {
            $xml .= '<Override PartName="/xl/worksheets/sheet'.$i.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        $xml .= '</Types>';

        return $xml;
    }

    protected static function rels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    protected static function workbook(array $sheets): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets>';
        foreach ($sheets as $i => $s) {
            $sheetId = $i + 1;
            $xml .= '<sheet name="'.self::esc($s['name']).'" sheetId="'.$sheetId.'" r:id="rId'.$sheetId.'"/>';
        }
        $xml .= '</sheets></workbook>';

        return $xml;
    }

    protected static function workbookRels(int $sheetCount): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        for ($i = 1; $i <= max($sheetCount, 1); $i++) {
            $xml .= '<Relationship Id="rId'.$i.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$i.'.xml"/>';
        }
        // FIX: target styles relatif terhadap xl/workbook.xml adalah "styles.xml", bukan "../styles.xml".
        $xml .= '<Relationship Id="rId'.(max($sheetCount, 1) + 1).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        $xml .= '</Relationships>';

        return $xml;
    }

    protected static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFDBEAFE"/><bgColor indexed="64"/></patternFill></fills>'
            .'<borders><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>'
            .'</styleSheet>';
    }
}
