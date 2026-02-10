<?php

namespace Lib;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Scraper
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Referer' => 'https://www.tokopedia.com/',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
            ],
            'timeout' => 30,
            'verify' => false // Shared hosting sometimes has SSL cert issues
        ]);
    }

    public function scrape($url)
    {
        try {
            $response = $this->client->get($url);
            $html = (string) $response->getBody();
            return $this->parseHtml($html, $url);
        } catch (RequestException $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to fetch URL: ' . $e->getMessage(),
                'url' => $url
            ];
        }
    }

    public function parseHtml($html, $url = '')
    {
        // Tokopedia usually stores product data in a JSON script variable
        // Look for window.__initialState__ or similar, but the most reliable way 
        // recently is via the Apollo state or JSON-LD

        $data = [];
        
        // 1. Try JSON-LD (Schema.org) - easiest for core details
        preg_match('/<script type="application\/ld\+json">([\s\S]*?)<\/script>/', $html, $jsonLdMatch);
        
        $productName = '';
        $description = '';
        $price = 0;
        $images = [];
        $weight = 1000; // Default
        
        if (!empty($jsonLdMatch[1])) {
            $jsonConfigs = $jsonLdMatch[1];
            // Sometimes there are multiple JSON-LD blocks, we need to iterate if so
            // But usually the product one is prominent.
            
            $jsonData = json_decode($jsonConfigs, true);
            
            // It might be an array of objects or a single object
            if (isset($jsonData['@type']) && $jsonData['@type'] === 'Product') {
                $productName = $jsonData['name'] ?? '';
                $description = $jsonData['description'] ?? '';
                $images = $jsonData['image'] ?? [];
                $price = $jsonData['offers']['price'] ?? 0;
            } else {
                 // Try to find in array
                if (is_array($jsonData)) {
                    foreach ($jsonData as $item) {
                        if (isset($item['@type']) && $item['@type'] === 'Product') {
                            $productName = $item['name'] ?? '';
                            $description = $item['description'] ?? '';
                            $images = $item['image'] ?? [];
                            $price = $item['offers']['price'] ?? 0;
                            break;
                        }
                    }
                }
            }
        }

        // 2. Fallback or additional details from window.__initialState__ 
        // This is needed for weight and variant details which JSON-LD often misses
        if (empty($weight) || empty($images)) {
             // Regex for specific product info if JSON-LD failed
             // This is brittle but necessary as backup
        }

        // Clean up data
        $productName = trim($productName);
        $description = strip_tags($description); // Shopee prefers plain text
        
        // Normalize images
        if (is_string($images)) {
            $images = [$images];
        }
        
        // Shopee max description length Check? (Shopee limit is around 5000 chars, usually fine)
        
        if (empty($productName)) {
             return [
                'status' => 'error',
                'message' => 'Could not parse product data. Cloudflare might be blocking.',
                'url' => $url
            ];
        }

        return [
            'status' => 'success',
            'data' => [
                'name' => $productName,
                'description' => $description,
                'price' => (int)$price,
                'weight' => (int)$weight, // Hard to get from HTML without complex parsing, default 1kg if missing
                'images' => array_slice($images, 0, 9), // Shopee max 9
                'url' => $url,
                'sku' => '' // Generated or empty
            ]
        ];
    }
}
