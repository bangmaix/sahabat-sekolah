<?php
$zip = new ZipArchive;
if ($zip->open('d:/DATA UUM/Project/www/sahabat-sekolah/docs/rancangan/PRD Final Terpadu — SAHABAT SEKOLAH.docx') === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $text = strip_tags($xml);
    echo substr($text, 0, 50000);
    $zip->close();
} else {
    echo "Failed to open zip";
}
