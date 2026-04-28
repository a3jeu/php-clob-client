# PHP CLOB Client

PHP client for the Polymarket CLOB (Central Limit Order Book) **V2**.

This is a PHP port of the official [Polymarket CLOB TypeScript client (V2)](https://github.com/Polymarket/clob-client-v2).

> **Note:** This library targets the **CLOB V2 API** (live since April 28, 2026). It is **not** compatible with the V1 API. See the [official V2 migration guide](https://docs.polymarket.com/v2-migration) for details on what changed.

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
use Polymarket\ClobClient\Types\UserOrder;
use Polymarket\ClobClient\Types\UserMarketOrder;
use Polymarket\ClobClient\Types\OrderType;

// Get your open orders
$openOrders = $client->getOpenOrders();

// Get your trades
$trades = $client->getTrades();

// Create and post a limit order
$order = new UserOrder(
    'token_id_here',
    0.55,  // price
    10,    // size
    Side::BUY
);
$result = $client->createAndPostOrder($order, null, OrderType::GTC);

// Limit order with builder attribution (optional)
$order = new UserOrder(
    'token_id_here',
    0.55,
    10,
    Side::BUY,
    null,  // expiration (null = no expiry)
    '0xYourBuilderCode000000000000000000000000000000000000000000000000' // builderCode (bytes32)
);

// Create and post a market order
$marketOrder = new UserMarketOrder(
    'token_id_here',
    25,        // amount in collateral (pUSD)
    Side::SELL,
    null,      // price (auto-calculated from order book)
    OrderType::FOK
);
$result = $client->createAndPostMarketOrder($marketOrder, null, OrderType::FOK);

// Cancel a specific order
$result = $client->cancelOrder('order_id_here');

// Cancel all orders
$result = $client->cancelAll();

// Cancel orders for a specific market
$result = $client->cancelMarketOrders('market_id', 'asset_id');

// Cancel multiple orders by hash
$result = $client->cancelOrders(['order_hash_1', 'order_hash_2']);
```

### Checking Balance and Allowance

```php
use Polymarket\ClobClient\Types\AssetType;

// Get balance and allowance for collateral (pUSD)
$balance = $client->getBalanceAllowance(AssetType::COLLATERAL->value);

// Get balance and allowance for a specific conditional token
$balance = $client->getBalanceAllowance(
    AssetType::CONDITIONAL->value,
    'token_id_here'
);
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

### UserOrder (limit order)

```php
new UserOrder(
    tokenID: 'token_id_here',
    price: 0.55,
    size: 10.0,
    side: Side::BUY,
    expiration: null,       // optional — Unix timestamp for GTD orders
    builderCode: null,      // optional — bytes32 builder attribution code
    metadata: null          // optional — bytes32 custom metadata
)
```

### UserMarketOrder (market order)

```php
new UserMarketOrder(
    tokenID: 'token_id_here',
    amount: 25.0,
    side: Side::SELL,
    price: null,            // optional — leave null to auto-calculate from order book
    orderType: OrderType::FOK,
    builderCode: null,      // optional — bytes32 builder attribution code
    metadata: null          // optional — bytes32 custom metadata
)
```

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
Chain::AMOY     // 80002 - Amoy Testnet (V2 addresses not yet documented)
```

### AssetType

```php
AssetType::COLLATERAL
AssetType::CONDITIONAL
```

## Configuration

Contract addresses are selected automatically by chain ID:

| Chain | Exchange | Collateral |
|-------|----------|-----------|
| Polygon (137) | `0xE111180000d2663C0091e4f400237545B87B996B` | pUSD `0xC011a7E12a19f7B1f670d46F03B03f3342E82DFB` |
| Amoy (80002) | V1 addresses (V2 not yet documented) | USDC.e |

### Collateral token: USDC.e → pUSD

V2 uses **pUSD** (Polymarket USD) as collateral instead of USDC.e. pUSD is a standard ERC-20 on Polygon backed by USDC. API-only traders need to wrap USDC.e into pUSD using the [CollateralOnramp contract](https://docs.polymarket.com/concepts/pusd) before trading.

## V2 API Changes Summary

Compared to the previous V1 client:

| What changed | V1 | V2 |
|---|---|---|
| EIP-712 domain version | `"1"` | `"2"` |
| Exchange contracts | Old addresses | New V2 addresses |
| Collateral token | USDC.e | pUSD |
| `feeRateBps` on orders | Required | **Removed** — fees set by protocol at match time |
| `nonce` on orders | Required | **Removed** — replaced by `timestamp` (ms) |
| `taker` on orders | Required | **Removed** |
| Builder attribution | HMAC headers | `builderCode` field on the order (bytes32) |

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
- ✅ Order creation with V2 EIP-712 signing
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

- [Official TypeScript Client V2](https://github.com/Polymarket/clob-client-v2)
- [Polymarket Documentation](https://docs.polymarket.com/)
- [V2 Migration Guide](https://docs.polymarket.com/v2-migration)

## Disclaimer

This is an unofficial PHP port of the Polymarket CLOB client. Use at your own risk.
