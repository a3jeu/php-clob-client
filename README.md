# PHP CLOB Client

PHP client for the Polymarket CLOB (Central Limit Order Book).

This is a PHP port of the official [Polymarket CLOB TypeScript client](https://github.com/Polymarket/clob-client).

## Requirements

- PHP 8.0 or higher
- Composer

## Installation

This package is not yet published on Packagist. Use one of the options below.

### Option A: Install from Git (recommended)

Add a VCS repository entry to your project's `composer.json`, then require the `dev-main` version:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/a3jeu/php-clob-client"
    }
  ]
}
```

```bash
composer require a3jeu/php-clob-client:dev-main
```

### Option B: Install from a local path

Clone this repository locally and reference it as a path repository:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../php-clob-client"
    }
  ]
}
```

```bash
composer require a3jeu/php-clob-client:@dev
```

## Usage

### Basic Setup

```php
<?php

require 'vendor/autoload.php';

use Polymarket\ClobClient\ClobClient;
use Polymarket\ClobClient\Types\Side;
use Polymarket\ClobClient\Types\OrderType;

// Configuration
$host = 'https://clob.polymarket.com';
$chainId = 137; // Polygon Mainnet
$privateKey = 'your_private_key_here'; // Without 0x prefix
$address = 'your_ethereum_address'; // Your Polymarket profile address

// Initialize the client
$client = new ClobClient($host, $chainId, $privateKey, $address);
```

### Creating or Deriving API Keys

Before placing orders, you need API credentials. It's recommended to derive existing keys rather than creating new ones:

```php
// Create or derive API key (recommended approach)
$creds = $client->createOrDeriveApiKey();

// Now reinitialize the client with credentials
$client = new ClobClient(
    $host,
    $chainId,
    $privateKey,
    $address,
    $creds,
    1, // signatureType: 0 for browser wallet, 1 for Magic/Email login
    $address // funder address
);
```

### Getting Market Data

```php
// Get server time
$time = $client->getServerTime();

// Get all markets
$markets = $client->getMarkets();

// Get specific market by condition ID
$market = $client->getMarket('0x...');

// Get order book for a token
$orderBook = $client->getOrderBook('token_id_here');

// Get current price
$price = $client->getPrice('token_id_here', Side::BUY);

// Get last trade price
$lastPrice = $client->getLastTradePrice('token_id_here');

// Get tick size
$tickSize = $client->getTickSize('token_id_here');

// Get negative risk flag
$negRisk = $client->getNegRisk('token_id_here');
```

### Managing Orders

```php
// Get your open orders
$openOrders = $client->getOpenOrders();

// Get your trades
$trades = $client->getTrades();

// Cancel a specific order
$result = $client->cancelOrder('order_id_here');

// Cancel all orders
$result = $client->cancelAll();

// Cancel orders for a specific market
$result = $client->cancelMarketOrders('market_id', 'asset_id');
```

### Checking Balance and Allowance

```php
use Polymarket\ClobClient\Types\AssetType;

// Get balance and allowance for collateral
$balance = $client->getBalanceAllowance(AssetType::COLLATERAL->value);

// Get balance and allowance for a specific conditional token
$balance = $client->getBalanceAllowance(
    AssetType::CONDITIONAL->value,
    'token_id_here'
);
```

### Equivalent PHP Example (Magic/Email Login Signature Type)

Below is a PHP equivalent to the Python snippet you shared. It derives an API key using signature type `1` (Magic/Email login), then fetches the USDC collateral balance.

```php
<?php

require 'vendor/autoload.php';

use Polymarket\ClobClient\ClobClient;
use Polymarket\ClobClient\Types\AssetType;

$host = 'https://clob.polymarket.com';
$chainId = 137; // Polygon mainnet
$privateKey = getenv('POLYMARKET_PRIVATE_KEY');
$address = getenv('POLYMARKET_ADDRESS');
$funder = getenv('POLYMARKET_PROXY_WALLET');

if (!$privateKey || !$address || !$funder) {
    throw new RuntimeException('Missing required POLYMARKET_* environment variables.');
}

$l1Client = new ClobClient(
    $host,
    $chainId,
    $privateKey,
    $address,
    null,
    1, // signature type 1
    $funder
);

$creds = $l1Client->deriveApiKey();

$client = new ClobClient(
    $host,
    $chainId,
    $privateKey,
    $address,
    $creds,
    1, // signature type 1
    $funder
);

$balance = $client->getBalanceAllowance(AssetType::COLLATERAL->value);
$usdc = ((float) ($balance['balance'] ?? 0)) / 1e6;

echo "Balance USDC with signature type 1: {$usdc}\n";
```

### Managing API Keys

```php
// Get all API keys
$keys = $client->getApiKeys();

// Delete current API key
$result = $client->deleteApiKey();

// Create a new API key
// Note: Credentials cannot be recovered after creation - store them safely!
$newCreds = $client->createApiKey();

// Derive existing API key
$existingCreds = $client->deriveApiKey();
```

**Important:** When creating new API credentials, they cannot be recovered if lost. Always store them securely immediately after creation.

## Types and Enums

### Side

```php
Side::BUY
Side::SELL
```

### OrderType

```php
OrderType::GTC  // Good Till Cancel
OrderType::FOK  // Fill or Kill
OrderType::GTD  // Good Till Date
OrderType::FAK  // Fill and Kill
```

### Chain

```php
Chain::POLYGON  // 137 - Polygon Mainnet
Chain::AMOY     // 80002 - Amoy Testnet
```

### AssetType

```php
AssetType::COLLATERAL
AssetType::CONDITIONAL
```

## Configuration

The client automatically configures contract addresses based on the chain ID:

- **Polygon (137)**: Production contracts
- **Amoy (80002)**: Test contracts

## Security Notes

⚠️ **Important Security Considerations:**

1. Never hardcode private keys in your source code
2. Store private keys securely using environment variables or secure key management systems
3. API credentials cannot be recovered once created - store them safely
4. Use read-only API keys when you only need to fetch data

## API Coverage

This PHP client covers the main CLOB API endpoints:

- ✅ Server time
- ✅ API key management (create, derive, get, delete)
- ✅ Market data (markets, order books, prices)
- ✅ Order management (get, post, cancel)
- ✅ Trade history
- ✅ Balance and allowance queries
- ⚠️ Order creation with signing (partial)
- ⚠️ RFQ functionality (not yet implemented)
- ⚠️ Builder API (not yet implemented)
- ⚠️ Rewards and earnings (not yet implemented)

## Examples

See the [examples](examples/) directory for more usage examples:

- `basic_usage.php` - Comprehensive example showing client initialization and various API calls
- `test_public_api.php` - Simple test of public API endpoints (no authentication required)

**Note:** Examples require network access to the Polymarket CLOB API at `https://clob.polymarket.com`.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT License - see LICENSE file for details

## Related Projects

- [Official TypeScript Client](https://github.com/Polymarket/clob-client)
- [Polymarket Documentation](https://docs.polymarket.com/)

## Disclaimer

This is an unofficial PHP port of the Polymarket CLOB client. Use at your own risk.
