# TypeScript to PHP Conversion Reference

## Side-by-Side Comparison

### Client Initialization

**TypeScript:**
```typescript
import { ClobClient, Side, OrderType } from "@polymarket/clob-client";
import { Wallet } from "@ethersproject/wallet";

const host = 'https://clob.polymarket.com';
const signer = new Wallet("private_key_here");
const creds = await new ClobClient(host, 137, signer).createOrDeriveApiKey();

const client = new ClobClient(host, 137, signer, await creds, 1, funder);
```

**PHP:**
```php
use Polymarket\ClobClient\ClobClient;
use Polymarket\ClobClient\Types\Side;
use Polymarket\ClobClient\Types\OrderType;

$host = 'https://clob.polymarket.com';
$privateKey = 'private_key_here';
$address = 'your_ethereum_address';

$client = new ClobClient($host, 137, $privateKey, $address);
$creds = $client->createOrDeriveApiKey();

$client = new ClobClient($host, 137, $privateKey, $address, $creds, 1, $address);
```

### Getting Markets

**TypeScript:**
```typescript
const markets = await client.getMarkets();
```

**PHP:**
```php
$markets = $client->getMarkets();
```

### Getting Order Book

**TypeScript:**
```typescript
const orderBook = await client.getOrderBook(tokenId);
```

**PHP:**
```php
$orderBook = $client->getOrderBook($tokenId);
```

### Getting Open Orders

**TypeScript:**
```typescript
const openOrders = await client.getOpenOrders();
```

**PHP:**
```php
$openOrders = $client->getOpenOrders();
```

### Canceling an Order

**TypeScript:**
```typescript
const result = await client.cancelOrder(orderId);
```

**PHP:**
```php
$result = $client->cancelOrder($orderId);
```

### Using Enums

**TypeScript:**
```typescript
import { Side, OrderType } from "@polymarket/clob-client";

const side = Side.BUY;
const orderType = OrderType.GTC;
```

**PHP:**
```php
use Polymarket\ClobClient\Types\Side;
use Polymarket\ClobClient\Types\OrderType;

$side = Side::BUY;
$orderType = OrderType::GTC;
```

## API Methods Comparison

| Method | TypeScript | PHP | Status |
|--------|-----------|-----|--------|
| Get Server Time | `getServerTime()` | `getServerTime()` | ✅ |
| Create API Key | `createApiKey()` | `createApiKey()` | ✅ |
| Derive API Key | `deriveApiKey()` | `deriveApiKey()` | ✅ |
| Create or Derive API Key | `createOrDeriveApiKey()` | `createOrDeriveApiKey()` | ✅ |
| Get API Keys | `getApiKeys()` | `getApiKeys()` | ✅ |
| Delete API Key | `deleteApiKey()` | `deleteApiKey()` | ✅ |
| Get Markets | `getMarkets()` | `getMarkets()` | ✅ |
| Get Market | `getMarket(conditionId)` | `getMarket($conditionId)` | ✅ |
| Get Order Book | `getOrderBook(tokenId)` | `getOrderBook($tokenId)` | ✅ |
| Get Midpoint | `getMidpoint(tokenId)` | `getMidpoint($tokenId)` | ✅ |
| Get Price | `getPrice(tokenId, side)` | `getPrice($tokenId, $side)` | ✅ |
| Get Last Trade Price | `getLastTradePrice(tokenId)` | `getLastTradePrice($tokenId)` | ✅ |
| Get Tick Size | `getTickSize(tokenId)` | `getTickSize($tokenId)` | ✅ |
| Get Neg Risk | `getNegRisk(tokenId)` | `getNegRisk($tokenId)` | ✅ |
| Get Open Orders | `getOpenOrders()` | `getOpenOrders()` | ✅ |
| Get Trades | `getTrades()` | `getTrades()` | ✅ |
| Post Order | `postOrder(signedOrder)` | `postOrder($signedOrder)` | ✅ |
| Cancel Order | `cancelOrder(orderId)` | `cancelOrder($orderId)` | ✅ |
| Cancel All | `cancelAll()` | `cancelAll()` | ✅ |
| Cancel Market Orders | `cancelMarketOrders(...)` | `cancelMarketOrders(...)` | ✅ |
| Get Balance Allowance | `getBalanceAllowance(...)` | `getBalanceAllowance(...)` | ✅ |
| Create and Post Order | `createAndPostOrder(...)` | `createAndPostOrder(...)` | ✅ |
| Create Market Order | `createMarketOrder(...)` | `createMarketOrder(...)` | ✅ |
| RFQ Methods | Various | - | ⚠️ Not Implemented |
| Builder Methods | Various | - | ⚠️ Not Implemented |

## Type Conversions

| TypeScript | PHP |
|-----------|-----|
| `interface ApiKeyCreds` | `class ApiKeyCreds` |
| `enum Side` | `enum Side: string` |
| `enum OrderType` | `enum OrderType: string` |
| `enum Chain` | `enum Chain: int` |
| `type UserOrder` | `class UserOrder` |
| `Promise<T>` | `T` (synchronous) |
| `async/await` | Synchronous calls |

## Key Differences

1. **Async vs Sync**: TypeScript uses async/await, PHP uses synchronous calls
2. **Signing**: TypeScript uses ethers.js, PHP uses simplito/elliptic-php
3. **HTTP Client**: TypeScript uses axios, PHP uses Guzzle
4. **Type System**: TypeScript uses interfaces, PHP uses classes with public properties
5. **Error Handling**: Both use exceptions, but PHP uses native Exception class

## What's Included

✅ Core CLOB client functionality
✅ API key management
✅ Market data retrieval
✅ Order management (query and cancel)
✅ Balance and allowance queries
✅ HMAC signing for API authentication
✅ EIP-712 signing for wallet authentication

## What's Not Included

⚠️ Full order creation with signing (requires order-utils port)
⚠️ RFQ (Request for Quote) functionality
⚠️ Builder API methods
⚠️ Rewards and earnings endpoints
⚠️ WebSocket support for real-time updates
⚠️ Price history endpoints
⚠️ Notification management
