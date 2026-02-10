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
            // Check if it's a blocking issue
            $isBlocked = strpos($result['message'], 'Status 410') !== false || strpos($result['message'], 'Status 403') !== false;
            
            echo "<div style='background-color: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 10px; margin-bottom: 10px; result-radius: 6px;'>";
            echo "<strong>Error scraping {$url}:</strong> " . htmlspecialchars($result['message']) . "<br>";
            if ($isBlocked) {
                echo "<div style='margin-top: 5px; font-weight: bold;'>⚠️ Hosting IP Blocked by Tokopedia. Please use the <a href='index.php' onclick='alert(\"Switch to the Manual HTML tab in the main page.\")' style='text-decoration: underline;'>Manual HTML</a> method.</div>";
            }
            echo "</div>";
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
        echo '<div style="font-family: sans-serif; max-width: 600px; margin: 50px auto; text-align: center; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
        echo '<h2 style="color: #e11d48;">Gagal Mengambil Data</h2>';
        echo '<p style="color: #4b5563; margin-bottom: 20px;">Tidak ada produk yang berhasil diextract. Kemungkinan semua URL diblokir atau input manual kosong.</p>';
        echo '<a href="index.php" style="display: inline-block; background-color: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; font-weight: bold;">&larr; Kembali ke Home</a>';
        echo '</div>';
        exit;
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
