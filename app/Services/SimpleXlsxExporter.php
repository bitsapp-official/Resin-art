<?php

namespace App\Services;

class SimpleXlsxExporter
{
    /**
     * Generate an OpenXML (.xlsx) binary string from headers and rows.
     * Pure PHP implementation using gzdeflate without requiring the ZipArchive extension.
     */
    public static function create(array $headers, array $rows, string $sheetName = 'Report'): string
    {
        $sheetXml = self::buildSheetXml($headers, $rows);
        $stylesXml = self::buildStylesXml();
        $workbookXml = self::buildWorkbookXml($sheetName);
        $workbookRelsXml = self::buildWorkbookRelsXml();
        $contentTypesXml = self::buildContentTypesXml();
        $relsXml = self::buildRelsXml();

        $files = [
            '[Content_Types].xml'        => $contentTypesXml,
            '_rels/.rels'                => $relsXml,
            'xl/_rels/workbook.xml.rels' => $workbookRelsXml,
            'xl/workbook.xml'            => $workbookXml,
            'xl/styles.xml'              => $stylesXml,
            'xl/worksheets/sheet1.xml'   => $sheetXml,
        ];

        return self::zipFiles($files);
    }

    /**
     * Convert 1-based column index to Excel letter (1 = A, 27 = AA).
     */
    private static function colLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $rem = ($colIndex - 1) % 26;
            $letter = chr(65 + $rem) . $letter;
            $colIndex = intdiv($colIndex - 1, 26);
        }
        return $letter;
    }

    /**
     * Build the primary worksheet XML with headers, data, and auto column widths.
     */
    private static function buildSheetXml(array $headers, array $rows): string
    {
        // Calculate estimated column widths
        $colWidths = [];
        foreach ($headers as $idx => $header) {
            $colWidths[$idx] = max(12, mb_strlen((string)$header) + 4);
        }
        foreach (array_slice($rows, 0, 100) as $row) {
            $c = 0;
            foreach ($row as $val) {
                if ($val instanceof \BackedEnum) {
                    $strVal = (string)$val->value;
                } elseif ($val instanceof \UnitEnum) {
                    $strVal = (string)$val->name;
                } elseif (is_object($val)) {
                    $strVal = method_exists($val, '__toString') ? (string)$val : '';
                } else {
                    $strVal = (string)$val;
                }
                $len = mb_strlen($strVal) + 3;
                if (isset($colWidths[$c])) {
                    $colWidths[$c] = min(50, max($colWidths[$c], $len));
                }
                $c++;
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
        $xml .= '  <sheetViews><sheetView tabSelected="1" workbookViewId="0"/></sheetViews>' . "\n";
        $xml .= '  <sheetFormatPr defaultRowHeight="18"/>' . "\n";

        // Column widths
        if (!empty($colWidths)) {
            $xml .= '  <cols>' . "\n";
            foreach ($colWidths as $idx => $width) {
                $colNum = $idx + 1;
                $xml .= '    <col min="' . $colNum . '" max="' . $colNum . '" width="' . $width . '" customWidth="1"/>' . "\n";
            }
            $xml .= '  </cols>' . "\n";
        }

        $xml .= '  <sheetData>' . "\n";

        // Header row: style 1 (Bold, Emerald Fill #065F46, White Text)
        $xml .= '    <row r="1" ht="26" customHeight="1">' . "\n";
        $c = 1;
        foreach ($headers as $h) {
            $cellRef = self::colLetter($c) . '1';
            $val = htmlspecialchars((string)$h, ENT_XML1, 'UTF-8');
            $xml .= '      <c r="' . $cellRef . '" s="1" t="inlineStr"><is><t>' . $val . '</t></is></c>' . "\n";
            $c++;
        }
        $xml .= '    </row>' . "\n";

        // Data rows
        $r = 2;
        foreach ($rows as $row) {
            $xml .= '    <row r="' . $r . '">' . "\n";
            $c = 1;
            foreach ($row as $rawVal) {
                if ($rawVal instanceof \BackedEnum) {
                    $val = (string)$rawVal->value;
                } elseif ($rawVal instanceof \UnitEnum) {
                    $val = (string)$rawVal->name;
                } elseif (is_object($rawVal)) {
                    $val = method_exists($rawVal, '__toString') ? (string)$rawVal : '';
                } else {
                    $val = $rawVal;
                }

                $cellRef = self::colLetter($c) . $r;
                if ($val === null || $val === '') {
                    $xml .= '      <c r="' . $cellRef . '" s="2"/>' . "\n";
                } elseif (is_numeric($val) && !preg_match('/^0[0-9]+/', (string)$val)) {
                    // Numeric value with style 2
                    $xml .= '      <c r="' . $cellRef . '" s="2"><v>' . $val . '</v></c>' . "\n";
                } else {
                    $escaped = htmlspecialchars((string)$val, ENT_XML1, 'UTF-8');
                    $xml .= '      <c r="' . $cellRef . '" s="2" t="inlineStr"><is><t>' . $escaped . '</t></is></c>' . "\n";
                }
                $c++;
            }
            $xml .= '    </row>' . "\n";
            $r++;
        }

        $xml .= '  </sheetData>' . "\n";
        $xml .= '</worksheet>';
        return $xml;
    }

    private static function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font>
      <sz val="10"/>
      <name val="Segoe UI"/>
      <color rgb="FF1F2937"/>
    </font>
    <font>
      <b/>
      <sz val="10.5"/>
      <name val="Segoe UI"/>
      <color rgb="FFFFFFFF"/>
    </font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill>
      <patternFill patternType="solid">
        <fgColor rgb="FF065F46"/>
      </patternFill>
    </fill>
  </fills>
  <borders count="2">
    <border>
      <left/><right/><top/><bottom/><diagonal/>
    </border>
    <border>
      <left style="thin"><color rgb="FFE5E7EB"/></left>
      <right style="thin"><color rgb="FFE5E7EB"/></right>
      <top style="thin"><color rgb="FFE5E7EB"/></top>
      <bottom style="thin"><color rgb="FFE5E7EB"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="3">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1">
      <alignment vertical="center"/>
    </xf>
  </cellXfs>
</styleSheet>';
    }

    private static function buildWorkbookXml(string $sheetName): string
    {
        $safeName = htmlspecialchars(substr(preg_replace('/[^\w\s-]/u', '', $sheetName) ?: 'Report', 0, 31), ENT_XML1, 'UTF-8');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="' . $safeName . '" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';
    }

    private static function buildWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
    }

    private static function buildContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
    }

    private static function buildRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
    }

    /**
     * Pure PHP PKZip builder (DEFLATE / uncompressed STORE).
     */
    private static function zipFiles(array $files): string
    {
        $entries = '';
        $centralDir = '';
        $offset = 0;

        foreach ($files as $name => $content) {
            $crc = crc32($content);
            $uncompressedSize = strlen($content);
            $compressed = gzdeflate($content, 6);
            if ($compressed !== false && strlen($compressed) < $uncompressedSize) {
                $compressionMethod = 8; // DEFLATE
                $data = $compressed;
                $compressedSize = strlen($compressed);
            } else {
                $compressionMethod = 0; // STORE
                $data = $content;
                $compressedSize = $uncompressedSize;
            }

            $nameLen = strlen($name);
            $time = 0;
            $date = (2026 - 1980) << 9 | (9 << 5) | 8;

            // Local file header (30 bytes + name)
            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034b50, // signature
                20,         // version needed
                0x0808,     // bit flag (UTF-8)
                $compressionMethod,
                $time,
                $date,
                $crc,
                $compressedSize,
                $uncompressedSize,
                $nameLen,
                0           // extra field len
            ) . $name;

            $entries .= $localHeader . $data;

            // Central directory entry (46 bytes + name)
            $cdEntry = pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50, // signature
                20,         // version made by
                20,         // version needed
                0x0808,     // bit flag
                $compressionMethod,
                $time,
                $date,
                $crc,
                $compressedSize,
                $uncompressedSize,
                $nameLen,
                0,          // extra len
                0,          // comment len
                0,          // disk start
                0,          // internal attr
                0,          // external attr
                $offset     // relative offset of local header
            ) . $name;

            $centralDir .= $cdEntry;
            $offset += strlen($localHeader) + strlen($data);
        }

        // End of central directory record (22 bytes)
        $cdSize = strlen($centralDir);
        $eocd = pack(
            'VvvvvVVv',
            0x06054b50, // signature
            0,          // disk number
            0,          // disk with CD
            count($files), // entries on this disk
            count($files), // total entries
            $cdSize,
            $offset,
            0           // comment len
        );

        return $entries . $centralDir . $eocd;
    }
}
