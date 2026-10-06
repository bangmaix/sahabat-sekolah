<?php
$zip = new ZipArchive;
if ($zip->open('d:\DATA UUM\Project\www\sahabat-sekolah\docs\rancangan\PRD Final Terpadu — SAHABAT SEKOLAH.docx') === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    $text = strip_tags(str_replace('<w:p', "\n<w:p", $xml));
    echo substr(preg_replace('/\s+/', ' ', $text), 0, 15000);
} else {
    echo 'failed';
}
