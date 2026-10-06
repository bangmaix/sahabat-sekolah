<?php
$zip = new ZipArchive;
if ($zip->open('d:/DATA UUM/Project/www/sahabat-sekolah/docs/rancangan/MVP_Technical_Implementation_Plan_SAHABAT_SEKOLAH_v1.0.docx') === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $text = strip_tags($xml);
    $text = preg_replace('/\s+/', ' ', $text); // Normalize whitespace
    
    // Find occurrences of risk assessment and print surrounding text
    $pos = 0;
    while (($pos = stripos($text, 'risiko', $pos)) !== false) {
        $start = max(0, $pos - 500);
        $end = min(strlen($text), $pos + 2000);
        echo "\n--- MATCH AT $pos ---\n";
        echo substr($text, $start, $end - $start);
        $pos += 10;
    }
    $pos = 0;
    while (($pos = stripos($text, 'skor', $pos)) !== false) {
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
