<?php

namespace App\Services;

use ZipArchive;

/**
 * Writer XLSX minimal tanpa dependensi tambahan.
 * Memakai ZipArchive + XML bawaan PHP sehingga bisa
 * menghasilkan file .xlsx valid yang dibuka di Excel/LibreOffice.
 */
class SimpleXlsxService
{
    /**
     * @param  array<int, array<int, mixed>>  $rows  Baris pertama = header.
     * @param  array<int, string>  $numericColumns  Huruf kolom yang diperlakukan sebagai angka (mis. ['H','I','J','K']).
     */
    public static function download(string $filename, array $headers, array $rows, array $numericColumns = []): mixed
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'xlsx').'.xlsx';
        @unlink($tmpPath);

        $zip = new ZipArchive;
        if ($zip->open($tmpPath, ZipArchive::CREATE) !== true) {
            abort(500, 'Gagal membuat file Excel.');
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypes());
        $zip->addFromString('_rels/.rels', self::rels());
        $zip->addFromString('xl/workbook.xml', self::workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels());
        $zip->addFromString('xl/styles.xml', self::styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::sheet($headers, $rows, $numericColumns));
        $zip->close();

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
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

    protected static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    protected static function sheet(array $headers, array $rows, array $numericColumns): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetData>';

        $rowNum = 1;
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
                    $xml .= '<c r="'.$col.$rowNum.'"><v>'.$val.'</v></c>';
                } else {
                    $xml .= '<c r="'.$col.$rowNum.'" t="inlineStr"><is><t>'.self::esc((string) ($val ?? '')).'</t></is></c>';
                }
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData>';
        // Freeze header + autofilter agar enak dipakai di Excel.
        $lastCol = self::colLetter(count($headers) - 1);
        $xml .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
        $xml .= '<autoFilter ref="A1:'.$lastCol.$rowNum.'"/>';
        $xml .= '</worksheet>';

        return $xml;
    }

    protected static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    protected static function rels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    protected static function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Laporan" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    protected static function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="../styles.xml"/>'
            .'</Relationships>';
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
