<?php
$zip = new ZipArchive;
if ($zip->open('d:/DATA UUM/Project/www/sahabat-sekolah/docs/rancangan/PRD Final Terpadu — SAHABAT SEKOLAH.docx') === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $text = strip_tags($xml);
    $text = preg_replace('/\s+/', ' ', $text); // Normalize whitespace
    
    // Find occurrences of risk assessment and print surrounding text
    $pos = 0;
    while (($pos = stripos($text, 'Penilaian Risiko', $pos)) !== false) {
        $start = max(0, $pos - 500);
        $end = min(strlen($text), $pos + 2000);
        echo "\n--- MATCH AT $pos ---\n";
        echo substr($text, $start, $end - $start);
        $pos += 10;
    }
    
    while (($pos = stripos($text, 'Risk Assessment', $pos)) !== false) {
        $start = max(0, $pos - 500);
        $end = min(strlen($text), $pos + 2000);
        echo "\n--- MATCH AT $pos ---\n";
        echo substr($text, $start, $end - $start);
        $pos += 10;
    }
    $zip->close();
} else {
    echo "Failed to open zip";
}
