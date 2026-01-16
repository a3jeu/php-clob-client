<?php

namespace Polymarket\ClobClient\Signing;

class HmacSigner
{
    /**
     * Builds the canonical Polymarket CLOB HMAC signature
     */
    public static function buildPolyHmacSignature(
        string $secret,
        int $timestamp,
        string $method,
        string $requestPath,
        ?string $body = null
    ): string {
        $message = $timestamp . $method . $requestPath;
        if ($body !== null) {
            $message .= $body;
        }

        // Decode the base64 secret
        $decodedSecret = base64_decode($secret);

        // Generate HMAC-SHA256 signature
        $signature = hash_hmac('sha256', $message, $decodedSecret, true);

        // Encode to base64
        $base64Sig = base64_encode($signature);

        // Convert to URL-safe base64 (but keep the = padding)
        $urlSafeSig = str_replace(['+', '/'], ['-', '_'], $base64Sig);

        return $urlSafeSig;
    }
}
