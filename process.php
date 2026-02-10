<?php

require 'vendor/autoload.php';

use Lib\Scraper;
use Lib\ExcelGenerator;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $urls = isset($_POST['urls']) ? explode("\n", $_POST['urls']) : [];
    $manualHtml = isset($_POST['manual_html']) ? $_POST['manual_html'] : '';
    
    $products = [];
    $scraper = new Scraper();

    // 1. Process URLs
    foreach ($urls as $url) {
        $url = trim($url);
        if (empty($url)) continue;
        
        $result = $scraper->scrape($url);
        
        if ($result['status'] === 'success') {
            $products[] = $result;
        } else {
            // Log error or show message?
            // For now, simple error output
            echo "Error scraping $url: " . $result['message'] . "<br>";
        }
    }

    // 2. Process Manual HTML if provided
    if (!empty($manualHtml)) {
        // We need a dummy URL for reference
        $manualResult = $scraper->parseHtml($manualHtml, 'Manual Input');
        if ($manualResult['status'] === 'success') {
            $products[] = $manualResult;
        } else {
             echo "Error parsing manual HTML: " . $manualResult['message'] . "<br>";
        }
    }

    if (empty($products)) {
        die("No products extracted. Please check your URLs or try the Manual method.");
    }

    // 3. Generate Excel
    $generator = new ExcelGenerator();
    $file = $generator->generate($products);
    
    // 4. Force Download
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="shopee_upload_' . date('Y-m-d_H-i-s') . '.xlsx"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    unlink($file); // Delete temp file
    exit;
}
