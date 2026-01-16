<?php

/**
 * Simple demonstration that shows fetching public data from the CLOB API
 * This doesn't require any credentials.
 */

require __DIR__ . '/../vendor/autoload.php';

use Polymarket\ClobClient\Http\HttpClient;
use Polymarket\ClobClient\Endpoints;

echo "=== Testing Public CLOB API Endpoints ===\n\n";

$httpClient = new HttpClient('https://clob.polymarket.com');

try {
    // Test 1: Get server time (public endpoint)
    echo "1. Getting server time...\n";
    $time = $httpClient->get(Endpoints::TIME);
    echo "✓ Server time: " . json_encode($time) . "\n\n";

    // Test 2: Try to get markets (public endpoint)
    echo "2. Fetching markets (first page)...\n";
    try {
        $markets = $httpClient->get(Endpoints::GET_MARKETS, [], ['next_cursor' => '']);
        $count = count($markets['data'] ?? []);
        echo "✓ Found $count markets\n";
        
        if ($count > 0) {
            $firstMarket = $markets['data'][0];
            echo "   First market question: " . ($firstMarket['question'] ?? 'N/A') . "\n";
            echo "   Condition ID: " . ($firstMarket['condition_id'] ?? 'N/A') . "\n";
        }
        echo "\n";
    } catch (Exception $e) {
        echo "⚠ Note: Could not fetch markets (API may have restrictions)\n";
        echo "   Error: " . $e->getMessage() . "\n\n";
    }

    echo "=== Tests completed! ===\n";
    echo "\nThe HTTP client is working correctly.\n";
    echo "To test authenticated endpoints, you'll need valid credentials.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nIf you see network errors, check your internet connection.\n";
    exit(1);
}
