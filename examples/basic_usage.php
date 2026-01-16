<?php

/**
 * Basic example demonstrating how to use the PHP CLOB Client
 * 
 * This example shows:
 * 1. Initializing the client
 * 2. Creating/deriving API keys
 * 3. Fetching market data
 * 4. Getting account information
 */

require __DIR__ . '/../vendor/autoload.php';

use Polymarket\ClobClient\ClobClient;
use Polymarket\ClobClient\Types\Side;
use Polymarket\ClobClient\Types\AssetType;

// Configuration - DO NOT hardcode these in production!
// Use environment variables or secure configuration management
$host = 'https://clob.polymarket.com';
$chainId = 137; // Polygon Mainnet

// These should come from environment variables or secure storage
$privateKey = getenv('POLYMARKET_PRIVATE_KEY');
$address = getenv('POLYMARKET_ADDRESS');

if (!$privateKey || !$address) {
    echo "ERROR: Required environment variables not set.\n";
    echo "Please set POLYMARKET_PRIVATE_KEY and POLYMARKET_ADDRESS\n";
    echo "\nFor testing, you can run:\n";
    echo "export POLYMARKET_PRIVATE_KEY='your_private_key_without_0x'\n";
    echo "export POLYMARKET_ADDRESS='0xYourEthereumAddress'\n";
    exit(1);
}

echo "=== Polymarket PHP CLOB Client Example ===\n\n";

try {
    // Step 1: Initialize client without API credentials
    echo "1. Initializing CLOB client...\n";
    $client = new ClobClient($host, $chainId, $privateKey, $address);
    echo "✓ Client initialized\n\n";

    // Step 2: Get server time (doesn't require authentication)
    echo "2. Getting server time...\n";
    $time = $client->getServerTime();
    echo "✓ Server time: " . json_encode($time) . "\n\n";

    // Step 3: Get markets (doesn't require authentication)
    echo "3. Fetching markets...\n";
    $markets = $client->getMarkets();
    echo "✓ Found " . count($markets['data'] ?? []) . " markets\n";
    
    if (!empty($markets['data'])) {
        $firstMarket = $markets['data'][0];
        echo "   First market: " . ($firstMarket['question'] ?? 'N/A') . "\n\n";
        
        // If there's a market with tokens, let's get more info
        if (!empty($firstMarket['tokens'])) {
            $tokenId = $firstMarket['tokens'][0]['token_id'];
            
            echo "4. Getting order book for token: $tokenId\n";
            $orderBook = $client->getOrderBook($tokenId);
            echo "✓ Order book retrieved\n";
            echo "   Bids: " . count($orderBook['bids'] ?? []) . "\n";
            echo "   Asks: " . count($orderBook['asks'] ?? []) . "\n\n";
            
            echo "5. Getting last trade price...\n";
            $lastPrice = $client->getLastTradePrice($tokenId);
            echo "✓ Last trade price: " . json_encode($lastPrice) . "\n\n";
        }
    }

    // Step 4: Create or derive API credentials
    echo "6. Creating/deriving API credentials...\n";
    echo "   (This will use existing credentials if available)\n";
    
    // Uncomment the following lines if you want to actually create credentials
    // WARNING: Only uncomment if you understand that credentials cannot be recovered!
    
    /*
    $creds = $client->createOrDeriveApiKey();
    echo "✓ API credentials obtained\n";
    echo "   Key: " . substr($creds->key, 0, 8) . "...\n";
    echo "   (Store these credentials securely!)\n\n";

    // Step 5: Reinitialize client with credentials for authenticated requests
    echo "7. Reinitializing client with API credentials...\n";
    $authenticatedClient = new ClobClient(
        $host,
        $chainId,
        $privateKey,
        $address,
        $creds,
        1, // signatureType: 1 for Magic/Email login
        $address // funder
    );
    echo "✓ Client reinitialized with authentication\n\n";

    // Step 6: Get open orders (requires authentication)
    echo "8. Getting open orders...\n";
    $openOrders = $authenticatedClient->getOpenOrders();
    echo "✓ Open orders: " . count($openOrders) . "\n\n";

    // Step 7: Get trades (requires authentication)
    echo "9. Getting trade history...\n";
    $trades = $authenticatedClient->getTrades();
    echo "✓ Trades: " . count($trades) . "\n\n";

    // Step 8: Get balance and allowance (requires authentication)
    echo "10. Getting balance and allowance...\n";
    $balance = $authenticatedClient->getBalanceAllowance(AssetType::COLLATERAL->value);
    echo "✓ Balance: " . ($balance['balance'] ?? 'N/A') . "\n";
    echo "   Allowance: " . ($balance['allowance'] ?? 'N/A') . "\n\n";
    */

    echo "=== Example completed successfully! ===\n";
    echo "\nNote: Authentication features are commented out for safety.\n";
    echo "Uncomment them when you're ready to test with real credentials.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
