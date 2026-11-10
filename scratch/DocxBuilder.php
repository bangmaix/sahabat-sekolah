<?php

class DocxBuilder
{
    private string $title;
    private string $subtitle;
    private string $roleName;
    private string $bodyXml = '';

    public function __construct(string $title, string $subtitle, string $roleName)
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->roleName = $roleName;
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1, 'UTF-8');
    }

    public function addTitle(string $text): self
    {
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:before="360" w:after="120"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
                    <w:b/>
                    <w:sz w:val="48"/>
                    <w:color w:val="0F274A"/>
                </w:rPr>
                <w:t>' . $this->escape($text) . '</w:t>
            </w:r>
        </w:p>';
        return $this;
    }

    public function addSubtitle(string $text): self
    {
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:before="0" w:after="360"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
                    <w:i/>
                    <w:sz w:val="26"/>
                    <w:color w:val="3B82F6"/>
                </w:rPr>
                <w:t>' . $this->escape($text) . '</w:t>
            </w:r>
        </w:p>';
        return $this;
    }

    public function addMetaTable(array $metadata): self
    {
        $this->bodyXml .= '<w:tbl>
            <w:tblPr>
                <w:tblW w:w="9600" w:type="dxa"/>
                <w:jc w:val="center"/>
                <w:tblBorders>
                    <w:top w:val="single" w:sz="6" w:space="0" w:color="CBD5E1"/>
                    <w:left w:val="none"/>
                    <w:bottom w:val="single" w:sz="6" w:space="0" w:color="CBD5E1"/>
                    <w:right w:val="none"/>
                    <w:insideH w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>
                    <w:insideV w:val="none"/>
                </w:tblBorders>
                <w:tblCellMar>
                    <w:top w:w="120" w:type="dxa"/>
                    <w:bottom w:w="120" w:type="dxa"/>
                    <w:left w:w="180" w:type="dxa"/>
                    <w:right w:w="180" w:type="dxa"/>
                </w:tblCellMar>
            </w:tblPr>';

        foreach ($metadata as $key => $val) {
            $this->bodyXml .= '<w:tr>
                <w:tc>
                    <w:tcPr>
                        <w:tcW w:w="2800" w:type="dxa"/>
                        <w:shd w:val="clear" w:color="auto" w:fill="F1F5F9"/>
                    </w:tcPr>
                    <w:p>
                        <w:pPr><w:spacing w:before="60" w:after="60"/></w:pPr>
                        <w:r>
                            <w:rPr><w:b/><w:sz w:val="20"/><w:color w:val="1E293B"/></w:rPr>
                            <w:t>' . $this->escape($key) . '</w:t>
                        </w:r>
                    </w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr>
                        <w:tcW w:w="6800" w:type="dxa"/>
                    </w:tcPr>
                    <w:p>
                        <w:pPr><w:spacing w:before="60" w:after="60"/></w:pPr>
                        <w:r>
                            <w:rPr><w:sz w:val="20"/><w:color w:val="334155"/></w:rPr>
                            <w:t>' . $this->escape($val) . '</w:t>
                        </w:r>
                    </w:p>
                </w:tc>
            </w:tr>';
        }

        $this->bodyXml .= '</w:tbl>';
        $this->addParagraph('');
        return $this;
    }

    public function addHeading1(string $text): self
    {
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:spacing w:before="400" w:after="160"/>
                <w:pBdr>
                    <w:bottom w:val="single" w:sz="14" w:space="4" w:color="1D4ED8"/>
                </w:pBdr>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
                    <w:b/>
                    <w:sz w:val="32"/>
                    <w:color w:val="0F274A"/>
                </w:rPr>
                <w:t>' . $this->escape($text) . '</w:t>
            </w:r>
        </w:p>';
        return $this;
    }

    public function addHeading2(string $text): self
    {
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:spacing w:before="280" w:after="100"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
                    <w:b/>
                    <w:sz w:val="26"/>
                    <w:color w:val="1E40AF"/>
                </w:rPr>
                <w:t>' . $this->escape($text) . '</w:t>
            </w:r>
        </w:p>';
        return $this;
    }

    public function addHeading3(string $text): self
    {
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:spacing w:before="200" w:after="80"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
                    <w:b/>
                    <w:sz w:val="22"/>
                    <w:color w:val="0369A1"/>
                </w:rPr>
                <w:t>' . $this->escape($text) . '</w:t>
            </w:r>
        </w:p>';
        return $this;
    }

    public function addParagraph(string $text, array $options = []): self
    {
        $bold = !empty($options['bold']);
        $italic = !empty($options['italic']);
        $color = $options['color'] ?? '334155';
        $align = $options['align'] ?? 'both';

        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:jc w:val="' . $align . '"/>
                <w:spacing w:before="40" w:after="120" w:line="276" w:lineRule="auto"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
                    ' . ($bold ? '<w:b/>' : '') . '
                    ' . ($italic ? '<w:i/>' : '') . '
                    <w:sz w:val="22"/>
                    <w:color w:val="' . $color . '"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $this->escape($text) . '</w:t>
            </w:r>
        </w:p>';
        return $this;
    }

    public function addBullet(string $boldPrefix, string $text, int $indent = 1): self
    {
        $indentVal = $indent * 360;
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:ind w:left="' . $indentVal . '" w:hanging="240"/>
                <w:spacing w:before="40" w:after="60" w:line="260" w:lineRule="auto"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Symbol" w:hAnsi="Symbol"/>
                    <w:color w:val="1D4ED8"/>
                </w:rPr>
                <w:t>  </w:t>
            </w:r>
            ' . (!empty($boldPrefix) ? '<w:r><w:rPr><w:b/><w:color w:val="0F274A"/><w:sz w:val="22"/></w:rPr><w:t xml:space="preserve">' . $this->escape($boldPrefix) . ' </w:t></w:r>' : '') . '
            <w:r>
                <w:rPr><w:color w:val="334155"/><w:sz w:val="22"/></w:rPr>
                <w:t xml:space="preserve">' . $this->escape($text) . '</w:t>
            </w:r>
        </w:p>';
        return $this;
    }

    public function addStep(int $stepNum, string $title, string $description): self
    {
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:ind w:left="480" w:hanging="480"/>
                <w:spacing w:before="120" w:after="40"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:b/>
                    <w:sz w:val="22"/>
                    <w:color w:val="1D4ED8"/>
                </w:rPr>
                <w:t>' . $stepNum . '. </w:t>
            </w:r>
            <w:r>
                <w:rPr>
                    <w:b/>
                    <w:sz w:val="22"/>
                    <w:color w:val="0F274A"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $this->escape($title) . ': </w:t>
            </w:r>
            <w:r>
                <w:rPr>
                    <w:sz w:val="22"/>
                    <w:color w:val="334155"/>
                </w:rPr>
                <w:t xml:space="preserve">' . $this->escape($description) . '</w:t>
            </w:r>
        </w:p>';
        return $this;
    }

    public function addCallout(string $title, string $message, string $type = 'info'): self
    {
        $colors = [
            'info' => ['border' => '2563EB', 'bg' => 'EFF6FF', 'title' => '1E40AF', 'icon' => 'ℹ️'],
            'warning' => ['border' => 'D97706', 'bg' => 'FFFBEB', 'title' => '92400E', 'icon' => '⚠️'],
            'danger' => ['border' => 'DC2626', 'bg' => 'FEF2F2', 'title' => '991B1B', 'icon' => '🚨'],
            'success' => ['border' => '16A34A', 'bg' => 'F0FDF4', 'title' => '166534', 'icon' => '✅'],
        ];

        $c = $colors[$type] ?? $colors['info'];

        $this->bodyXml .= '<w:tbl>
            <w:tblPr>
                <w:tblW w:w="9600" w:type="dxa"/>
                <w:jc w:val="center"/>
                <w:tblBorders>
                    <w:top w:val="none"/>
                    <w:left w:val="single" w:sz="36" w:space="0" w:color="' . $c['border'] . '"/>
                    <w:bottom w:val="none"/>
                    <w:right w:val="none"/>
                </w:tblBorders>
                <w:tblCellMar>
                    <w:top w:w="160" w:type="dxa"/>
                    <w:bottom w:w="160" w:type="dxa"/>
                    <w:left w:w="240" w:type="dxa"/>
                    <w:right w:w="240" w:type="dxa"/>
                </w:tblCellMar>
            </w:tblPr>
            <w:tr>
                <w:tc>
                    <w:tcPr>
                        <w:tcW w:w="9600" w:type="dxa"/>
                        <w:shd w:val="clear" w:color="auto" w:fill="' . $c['bg'] . '"/>
                    </w:tcPr>
                    <w:p>
                        <w:pPr><w:spacing w:before="40" w:after="40"/></w:pPr>
                        <w:r>
                            <w:rPr><w:b/><w:sz w:val="22"/><w:color w:val="' . $c['title'] . '"/></w:rPr>
                            <w:t>' . $c['icon'] . ' ' . $this->escape($title) . '</w:t>
                        </w:r>
                    </w:p>
                    <w:p>
                        <w:pPr><w:spacing w:before="0" w:after="40"/></w:pPr>
                        <w:r>
                            <w:rPr><w:sz w:val="20"/><w:color w:val="334155"/></w:rPr>
                            <w:t xml:space="preserve">' . $this->escape($message) . '</w:t>
                        </w:r>
                    </w:p>
                </w:tc>
            </w:tr>
        </w:tbl>';
        $this->addParagraph('');
        return $this;
    }

    public function addTable(array $headers, array $rows, array $widths = []): self
    {
        $colCount = count($headers);
        if (empty($widths)) {
            $defaultColW = (int)(9600 / max(1, $colCount));
            $widths = array_fill(0, $colCount, $defaultColW);
        }

        $this->bodyXml .= '<w:tbl>
            <w:tblPr>
                <w:tblW w:w="9600" w:type="dxa"/>
                <w:jc w:val="center"/>
                <w:tblBorders>
                    <w:top w:val="single" w:sz="8" w:space="0" w:color="CBD5E1"/>
                    <w:left w:val="single" w:sz="8" w:space="0" w:color="CBD5E1"/>
                    <w:bottom w:val="single" w:sz="8" w:space="0" w:color="CBD5E1"/>
                    <w:right w:val="single" w:sz="8" w:space="0" w:color="CBD5E1"/>
                    <w:insideH w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>
                    <w:insideV w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/>
                </w:tblBorders>
                <w:tblCellMar>
                    <w:top w:w="120" w:type="dxa"/>
                    <w:bottom w:w="120" w:type="dxa"/>
                    <w:left w:w="160" w:type="dxa"/>
                    <w:right w:w="160" w:type="dxa"/>
                </w:tblCellMar>
            </w:tblPr>';

        // Header Row
        $this->bodyXml .= '<w:tr>
            <w:trPr><w:tblHeader/></w:trPr>';
        foreach ($headers as $i => $h) {
            $w = $widths[$i] ?? 2000;
            $this->bodyXml .= '<w:tc>
                <w:tcPr>
                    <w:tcW w:w="' . $w . '" w:type="dxa"/>
                    <w:shd w:val="clear" w:color="auto" w:fill="0F274A"/>
                </w:tcPr>
                <w:p>
                    <w:pPr><w:jc w:val="center"/><w:spacing w:before="60" w:after="60"/></w:pPr>
                    <w:r>
                        <w:rPr><w:b/><w:sz w:val="20"/><w:color w:val="FFFFFF"/></w:rPr>
                        <w:t>' . $this->escape($h) . '</w:t>
                    </w:r>
                </w:p>
            </w:tc>';
        }
        $this->bodyXml .= '</w:tr>';

        // Data Rows
        foreach ($rows as $rIdx => $row) {
            $bg = ($rIdx % 2 === 1) ? 'F8FAFC' : 'FFFFFF';
            $this->bodyXml .= '<w:tr>';
            foreach ($row as $cIdx => $val) {
                $w = $widths[$cIdx] ?? 2000;
                $align = ($cIdx === 0 && count($row) > 3) ? 'center' : 'left';
                $this->bodyXml .= '<w:tc>
                    <w:tcPr>
                        <w:tcW w:w="' . $w . '" w:type="dxa"/>
                        <w:shd w:val="clear" w:color="auto" w:fill="' . $bg . '"/>
                    </w:tcPr>
                    <w:p>
                        <w:pPr><w:jc w:val="' . $align . '"/><w:spacing w:before="60" w:after="60"/></w:pPr>
                        <w:r>
                            <w:rPr><w:sz w:val="20"/><w:color w:val="334155"/></w:rPr>
                            <w:t xml:space="preserve">' . $this->escape((string)$val) . '</w:t>
                        </w:r>
                    </w:p>
                </w:tc>';
            }
            $this->bodyXml .= '</w:tr>';
        }

        $this->bodyXml .= '</w:tbl>';
        $this->addParagraph('');
        return $this;
    }

    public function addDivider(): self
    {
        $this->bodyXml .= '<w:p>
            <w:pPr>
                <w:spacing w:before="160" w:after="160"/>
                <w:pBdr>
                    <w:bottom w:val="single" w:sz="6" w:space="1" w:color="E2E8F0"/>
                </w:pBdr>
            </w:pPr>
        </w:p>';
        return $this;
    }

    public function addPageBreak(): self
    {
        $this->bodyXml .= '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
        return $this;
    }

    public function save(string $filepath): bool
    {
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (file_exists($filepath)) {
            unlink($filepath);
        }

        $zip = new ZipArchive();
        if ($zip->open($filepath, ZipArchive::CREATE) !== true) {
            return false;
        }

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
  <Override PartName="/word/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"/>
  <Override PartName="/word/fontTable.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.fontTable+xml"/>
</Types>';

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>';

        $docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/settings" Target="settings.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/fontTable" Target="fontTable.xml"/>
</Relationships>';

        $fontTable = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:fonts xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:font w:name="Calibri"/>
  <w:font w:name="Calibri Light"/>
</w:fonts>';

        $settings = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:defaultTabStop w:val="720"/>
  <w:characterSpacingControl w:val="doNotCompress"/>
</w:settings>';

        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:docDefaults>
    <w:rPrDefault>
      <w:rPr>
        <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/>
        <w:sz w:val="22"/>
        <w:color w:val="334155"/>
      </w:rPr>
    </w:rPrDefault>
  </w:docDefaults>
</w:styles>';

        $docXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    ' . $this->bodyXml . '
    <w:sectPr>
      <w:pgSz w:w="12240" w:h="15840"/>
      <w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>
    </w:sectPr>
  </w:body>
</w:document>';

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('word/_rels/document.xml.rels', $docRels);
        $zip->addFromString('word/fontTable.xml', $fontTable);
        $zip->addFromString('word/settings.xml', $settings);
        $zip->addFromString('word/styles.xml', $styles);
        $zip->addFromString('word/document.xml', $docXml);

        $zip->close();
        return true;
    }
}
