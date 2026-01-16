# Development Notes

## Conversion from TypeScript to PHP

This repository is a PHP port of the [Polymarket CLOB TypeScript client](https://github.com/Polymarket/clob-client).

### Key Differences

1. **Type System**: 
   - TypeScript interfaces → PHP classes with public properties
   - TypeScript enums → PHP 8.0+ backed enums
   - TypeScript union types → PHP union types (where supported)

2. **Async/Await**:
   - TypeScript uses async/await for API calls
   - PHP uses synchronous HTTP requests via Guzzle

3. **Signing**:
   - TypeScript uses `ethers.js` and Web Crypto API
   - PHP uses `simplito/elliptic-php` for EIP-712 signing and native `hash_hmac` for HMAC

4. **Dependencies**:
   - TypeScript: ethers, axios, @polymarket/* packages
   - PHP: guzzlehttp/guzzle, kornrunner/keccak, simplito/elliptic-php

### Implementation Status

#### Completed
- ✅ Core types and enums (Side, OrderType, Chain, AssetType)
- ✅ HTTP client with Guzzle
- ✅ HMAC signing for API key authentication
- ✅ EIP-712 signing for wallet authentication
- ✅ Main ClobClient class with core methods:
  - API key management (create, derive, delete, get)
  - Market data (markets, orderbooks, prices, tick sizes)
  - Order management (get orders, trades, cancel)
  - Balance and allowance queries
- ✅ Configuration and constants
- ✅ Comprehensive README
- ✅ Example usage

#### Not Yet Implemented
- ⚠️ Order building and submission (requires full order signing)
- ⚠️ RFQ (Request for Quote) functionality
- ⚠️ Builder API endpoints
- ⚠️ Rewards and earnings endpoints
- ⚠️ WebSocket support for real-time data
- ⚠️ Comprehensive unit tests
- ⚠️ Price history endpoints
- ⚠️ Notification management

### Testing

Run basic tests:
```bash
php tests/basic_test.php
```

Run example:
```bash
php examples/basic_usage.php
```

### Contributing

When adding new features:
1. Follow PSR-12 coding standards
2. Add type hints for all parameters and return types
3. Document public methods with PHPDoc
4. Add tests for new functionality
5. Update README with new features

### Security Considerations

1. **Private Keys**: Never commit private keys. Use environment variables.
2. **API Credentials**: Store securely and never log them.
3. **EIP-712 Signing**: The implementation uses simplito/elliptic-php which may not be audited for production use.
4. **HMAC Signing**: Uses PHP's native hash_hmac which is considered secure.

### Known Limitations

1. **No WebSocket Support**: The TypeScript client has WebSocket support for real-time updates. This is not yet implemented in PHP.
2. **Synchronous Only**: All API calls are synchronous, unlike the async TypeScript version.
3. **Limited Order Building**: Full order creation with EIP-712 signing is not fully implemented.
4. **No Browser Support**: Unlike the TypeScript version, this cannot run in a browser environment.

### Future Improvements

- Add full order creation and signing
- Implement RFQ functionality
- Add WebSocket support for real-time data
- Add comprehensive test suite
- Add CI/CD integration
- Publish to Packagist
- Add code quality tools (PHPStan, Psalm)
