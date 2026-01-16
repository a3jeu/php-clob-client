<?php

namespace Polymarket\ClobClient\Headers;

use Polymarket\ClobClient\Signing\Eip712Signer;
use Polymarket\ClobClient\Signing\HmacSigner;
use Polymarket\ClobClient\Types\ApiKeyCreds;

class HeaderBuilder
{
    /**
     * Create L1 headers (EIP-712 signature)
     */
    public static function createL1Headers(
        string $privateKey,
        string $address,
        int $chainId,
        ?int $nonce = null,
        ?int $timestamp = null
    ): array {
        $ts = $timestamp ?? time();
        $n = $nonce ?? 0;

        $signature = Eip712Signer::buildClobEip712Signature(
            $privateKey,
            $chainId,
            $ts,
            $n,
            $address
        );

        return [
            'POLY_ADDRESS' => $address,
            'POLY_SIGNATURE' => $signature,
            'POLY_TIMESTAMP' => (string)$ts,
            'POLY_NONCE' => (string)$n,
        ];
    }

    /**
     * Create L2 headers (HMAC signature with API keys)
     */
    public static function createL2Headers(
        string $address,
        ApiKeyCreds $creds,
        string $method,
        string $requestPath,
        ?string $body = null,
        ?int $timestamp = null
    ): array {
        $ts = $timestamp ?? time();

        $signature = HmacSigner::buildPolyHmacSignature(
            $creds->secret,
            $ts,
            $method,
            $requestPath,
            $body
        );

        return [
            'POLY_ADDRESS' => $address,
            'POLY_SIGNATURE' => $signature,
            'POLY_TIMESTAMP' => (string)$ts,
            'POLY_API_KEY' => $creds->key,
            'POLY_PASSPHRASE' => $creds->passphrase,
        ];
    }
}
