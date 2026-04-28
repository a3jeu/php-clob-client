<?php

/**
 * Simple test to verify the client can be instantiated and basic methods work
 */

require __DIR__ . '/../vendor/autoload.php';

use Polymarket\ClobClient\ClobClient;
use Polymarket\ClobClient\Types\Side;
use Polymarket\ClobClient\Config;

echo "Testing PHP CLOB Client...\n\n";

// Test 1: Instantiate the client
echo "Test 1: Instantiating client... ";
try {
    $client = new ClobClient(
        'https://clob.polymarket.com',
        137,
        'test_private_key',
        '0xTestAddress'
    );
    echo "✓ PASS\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Test enums
echo "Test 2: Testing enums... ";
try {
    $buySide = Side::BUY;
    $sellSide = Side::SELL;
    assert($buySide->value === 'BUY');
    assert($sellSide->value === 'SELL');
    echo "✓ PASS\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Test Config
echo "Test 3: Testing config... ";
try {
    $config = Config::getContractConfig(137);
    assert(isset($config['exchange']));
    assert(isset($config['collateral']));
    // V2 exchange addresses
    assert($config['exchange'] === '0xE111180000d2663C0091e4f400237545B87B996B', 'V2 exchange address mismatch');
    assert($config['collateral'] === '0xC011a7E12a19f7B1f670d46F03B03f3342E82DFB', 'V2 pUSD collateral address mismatch');
    echo "✓ PASS\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Test HMAC signing
echo "Test 4: Testing HMAC signing... ";
try {
    $signature = \Polymarket\ClobClient\Signing\HmacSigner::buildPolyHmacSignature(
        base64_encode('fake_test_secret_for_unit_tests_only'),
        time(),
        'GET',
        '/test'
    );
    assert(!empty($signature));
    echo "✓ PASS\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 5: Test bytes32 normalization
echo "Test 5: Testing bytes32 validation... ";
try {
    $valid = \Polymarket\ClobClient\OrderBuilder\OrderUtils::normalizeBytes32(
        '0x0000000000000000000000000000000000000000000000000000000000000000'
    );
    assert($valid === '0x0000000000000000000000000000000000000000000000000000000000000000');

    $invalidTriggered = false;
    try {
        \Polymarket\ClobClient\OrderBuilder\OrderUtils::normalizeBytes32('0xdeadbeef');
    } catch (\InvalidArgumentException $e) {
        $invalidTriggered = true;
    }
    assert($invalidTriggered, 'Should have thrown for short bytes32');
    echo "✓ PASS\n";
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== All tests passed! ===\n";
