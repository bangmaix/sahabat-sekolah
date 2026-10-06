<?php
$zip = new ZipArchive;
$file = 'd:/DATA UUM/Project/www/sahabat-sekolah/docs/rancangan/PRD Final Terpadu — SAHABAT SEKOLAH.docx';
if ($zip->open($file) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    
    $paragraphs = $dom->getElementsByTagName('p');
    $output = '';
    
    foreach ($paragraphs as $p) {
        $text = '';
        $texts = $p->getElementsByTagName('t');
        foreach ($texts as $t) {
            $text .= $t->nodeValue;
        }
        if (trim($text) != '') {
            $output .= trim($text) . "\n";
        }
    }
    
    file_put_contents('scratch-clean.txt', $output);
    $zip->close();
} else {
    echo "Failed to open zip";
}
