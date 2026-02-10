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
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language' => 'en-US,en;q=0.9,id;q=0.8',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Referer' => 'https://www.google.com/',
                'Upgrade-Insecure-Requests' => '1',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'cross-site',
                'Sec-Fetch-User' => '?1',
                'TE' => 'trailers',
            ],
            'timeout' => 30,
            'connect_timeout' => 30,
            'verify' => false,
            'http_errors' => false // Prevent Guzzle from throwing exceptions on 4xx/5xx so we can handle them
        ]);
    }

    public function scrape($url)
    {
        try {
            $response = $this->client->get($url);
            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                return [
                    'status' => 'error',
                    'message' => "Failed to fetch URL (Status $statusCode). Tokopedia detected the scraper. Please use the 'Manual HTML' tab.",
                    'url' => $url
                ];
            }

            $html = (string) $response->getBody();
            return $this->parseHtml($html, $url);
        } catch (RequestException $e) {
            return [
                'status' => 'error',
                'message' => 'Network error: ' . $e->getMessage(),
                'url' => $url
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'General error: ' . $e->getMessage(),
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

        // 2. Fallback: Open Graph / Meta Tags (High reliability)
        if (empty($productName)) {
            // Name
            if (preg_match('/<meta property="og:title" content="([^"]+)"/', $html, $m)) {
                $productName = $m[1];
            } elseif (preg_match('/<title>([^<]+)<\/title>/', $html, $m)) {
                $productName = str_replace('| Tokopedia', '', $m[1]);
            }

            // Description
            if (preg_match('/<meta property="og:description" content="([^"]+)"/', $html, $m)) {
                $description = $m[1];
            }

            // Image
            if (preg_match('/<meta property="og:image" content="([^"]+)"/', $html, $m)) {
                $images[] = $m[1];
            }

            // Price (try og:price:amount or product:price:amount)
            if (preg_match('/<meta property="product:price:amount" content="([^"]+)"/', $html, $m)) {
                $price = $m[1];
            } elseif (preg_match('/<meta property="og:price:amount" content="([^"]+)"/', $html, $m)) {
                $price = $m[1];
            } elseif (preg_match('/"price":\s*(\d+)/', $html, $m)) {
                 // Try loose JSON regex
                 $price = $m[1];
            }
        }

        // 3. Fallback: Bruteforce Regex for window.__initialState__ (Common in React apps)
        if (empty($productName) || empty($images)) {
             if (preg_match('/"pName":"([^"]+)"/', $html, $m)) {
                 $productName = $productName ?: $m[1];
             }
             if (preg_match('/"pDesc":"([^"]+)"/', $html, $m)) {
                 $description = $description ?: $m[1];
             }
             if (preg_match('/"price":(\d+)/', $html, $m)) {
                 $price = $price ?: $m[1];
             }
        }
        
        // 4. Clean up Price (ensure it's int)
        // Sometimes price comes as "Rp 10.000" or similar in text
        if (is_string($price)) {
            $price = preg_replace('/[^0-9]/', '', $price);
        }

        // Clean up data
        $productName = trim(html_entity_decode($productName));
        $description = trim(html_entity_decode(strip_tags($description)));
        
        // Normalize images
        if (is_string($images)) {
            $images = [$images];
        }

        if (empty($productName)) {
            return [
                'status' => 'error',
                'message' => 'Gagal memparsing HTML. Pastikan Anda meng-copy FULL source code (Ctrl+U -> Ctrl+A -> Ctrl+C).',
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
