<?php

namespace Polymarket\ClobClient\Signing;

use Elliptic\EC;
use kornrunner\Keccak;
use Polymarket\ClobClient\OrderBuilder\OrderUtils;

class OrderSigner
{
    private const DOMAIN_NAME = 'Polymarket CTF Exchange';
    private const DOMAIN_VERSION = '1';
    private const DOMAIN_TYPE = 'EIP712Domain(string name,string version,uint256 chainId,address verifyingContract)';
    private const ORDER_TYPE = 'Order(uint256 salt,address maker,address signer,address taker,uint256 tokenId,uint256 makerAmount,uint256 takerAmount,uint256 expiration,uint256 nonce,uint256 feeRateBps,uint8 side,uint8 signatureType)';

    public static function signOrder(array $order, int $chainId, string $exchangeAddress, string $privateKey): string
    {
        $domainSeparator = self::hashDomain($chainId, $exchangeAddress);
        $orderHash = self::hashOrder($order);

        $eip712Hash = Keccak::hash("\x19\x01" . hex2bin($domainSeparator) . hex2bin($orderHash), 256);

        return self::signHash($eip712Hash, $privateKey);
    }

    private static function hashDomain(int $chainId, string $exchangeAddress): string
    {
        $typeHash = Keccak::hash(self::DOMAIN_TYPE, 256);
        $encoded = $typeHash
            . self::encodeString(self::DOMAIN_NAME)
            . self::encodeString(self::DOMAIN_VERSION)
            . self::encodeUint((string)$chainId)
            . self::encodeAddress($exchangeAddress);

        return Keccak::hash(hex2bin($encoded), 256);
    }

    private static function hashOrder(array $order): string
    {
        $typeHash = Keccak::hash(self::ORDER_TYPE, 256);
        $encoded = $typeHash
            . self::encodeUint((string)$order['salt'])
            . self::encodeAddress($order['maker'])
            . self::encodeAddress($order['signer'])
            . self::encodeAddress($order['taker'])
            . self::encodeUint((string)$order['tokenId'])
            . self::encodeUint((string)$order['makerAmount'])
            . self::encodeUint((string)$order['takerAmount'])
            . self::encodeUint((string)$order['expiration'])
            . self::encodeUint((string)$order['nonce'])
            . self::encodeUint((string)$order['feeRateBps'])
            . self::encodeUint((string)$order['side'])
            . self::encodeUint((string)$order['signatureType']);

        return Keccak::hash(hex2bin($encoded), 256);
    }

    private static function encodeString(string $value): string
    {
        return Keccak::hash($value, 256);
    }

    private static function encodeAddress(string $address): string
    {
        $normalized = OrderUtils::normalizeAddress($address);
        $hex = str_replace('0x', '', $normalized);
        return str_pad($hex, 64, '0', STR_PAD_LEFT);
    }

    private static function encodeUint(string $value): string
    {
        $hex = self::decimalToHex($value);
        return str_pad($hex, 64, '0', STR_PAD_LEFT);
    }

    private static function decimalToHex(string $value): string
    {
        $value = ltrim($value, '+');
        if ($value === '' || $value === '0') {
            return '0';
        }

        if (function_exists('gmp_init')) {
            return gmp_strval(gmp_init($value, 10), 16);
        }

        $decimal = $value;
        $hex = '';
        while ($decimal !== '0') {
            [$decimal, $remainder] = self::divmod($decimal, 16);
            $hex = dechex($remainder) . $hex;
        }

        return $hex === '' ? '0' : $hex;
    }

    private static function divmod(string $number, int $divisor): array
    {
        $length = strlen($number);
        $quotient = '';
        $remainder = 0;

        for ($i = 0; $i < $length; $i++) {
            $digit = (int)$number[$i];
            $temp = $remainder * 10 + $digit;
            $digitQuotient = intdiv($temp, $divisor);
            $remainder = $temp % $divisor;
            if (!($digitQuotient === 0 && $quotient === '')) {
                $quotient .= (string)$digitQuotient;
            }
        }

        if ($quotient === '') {
            $quotient = '0';
        }

        return [$quotient, $remainder];
    }

    private static function signHash(string $hash, string $privateKey): string
    {
        $ec = new EC('secp256k1');
        $privateKey = str_replace('0x', '', $privateKey);

        $key = $ec->keyFromPrivate($privateKey, 'hex');
        $signature = $key->sign($hash, ['canonical' => true]);

        $r = str_pad($signature->r->toString(16), 64, '0', STR_PAD_LEFT);
        $s = str_pad($signature->s->toString(16), 64, '0', STR_PAD_LEFT);
        $v = dechex($signature->recoveryParam + 27);
        $v = str_pad($v, 2, '0', STR_PAD_LEFT);

        return '0x' . $r . $s . $v;
    }
}
