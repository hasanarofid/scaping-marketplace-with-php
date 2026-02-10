<?php

namespace Lib;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelGenerator
{
    public function generate($products, $filename = 'shopee_upload.xlsx')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Set Headers (Shopee Basic Template Standard)
        // These are standard headers, might need adjustment based on specific category but this is the general format
        $headers = [
            'Category ID', // 1
            'Product Name', // 2
            'Product Description', // 3
            'Parent SKU', // 4
            'Variation Name', // 5
            'Variation Option', // 6
            'Image 1', // 7
            'Image 2', // 8
            'Image 3', // 9
            'Image 4', // 10
            'Image 5', // 11
            'Image 6', // 12
            'Image 7', // 13
            'Image 8', // 14
            'Image 9', // 15
            'Price', // 16
            'Stock', // 17
            'Weight', // 18
            'Length', // 19
            'Width', // 20
            'Height', // 21
            'Pre-Order DTS', // 22
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // 2. Fill Data
        $row = 2; // Start from 2nd row (or maybe 4th if there are instruction rows, but basic usually starts at 2 or 5 depending on template version. simpler is better for now)
        // Note: Shopee sometimes requires headers on row 4 or 5. Safest is row 1 for clean csv/omni-channel tools, but for direct mass upload, let's stick to row 1 header for now or user can copy-paste.
        
        foreach ($products as $product) {
            $data = $product['data'];
            
            $sheet->setCellValue('A' . $row, ''); // Category ID - User must fill
            $sheet->setCellValue('B' . $row, mb_substr($data['name'], 0, 100)); // Max 120 chars commonly
            $sheet->setCellValue('C' . $row, $data['description']); 
            $sheet->setCellValue('D' . $row, ''); // Parent SKU
            
            // Single variation logic for now
            $sheet->setCellValue('E' . $row, ''); // Variation Name
            $sheet->setCellValue('F' . $row, ''); // Variation Option
            
            // Images
            $imgCol = 'G';
            foreach ($data['images'] as $img) {
                // Ensure image is direct URL (scrape usually gives high res)
                $sheet->setCellValue($imgCol . $row, $img);
                $imgCol++;
                if ($imgCol > 'O') break; // Max 9 images (G to O)
            }
            
            $sheet->setCellValue('P' . $row, $data['price']); 
            $sheet->setCellValue('Q' . $row, 100); // Default Stock
            $sheet->setCellValue('R' . $row, 1); // Default Weight 1kg if missing
            $sheet->setCellValue('S' . $row, ''); // L
            $sheet->setCellValue('T' . $row, ''); // W
            $sheet->setCellValue('U' . $row, ''); // H
            $sheet->setCellValue('V' . $row, ''); // DTS
            
            $row++;
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);
        
        // Save to temp file
        $tempFile = sys_get_temp_dir() . '/' . $filename;
        $writer->save($tempFile);
        
        return $tempFile;
    }
}
