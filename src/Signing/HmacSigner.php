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
        $base64Secret = base64_decode(strtr($secret, '-_', '+/'));
        $message = $timestamp . $method . $requestPath;
        
        if ($body !== null) {
            // NOTE: Necessary to use JSON encoding to match go and typescript
            if (is_array($body) || is_object($body)) {
                $message .= json_encode($body);
            } else {
                $message .= $body;
            }
        }
        
        $hash = hash_hmac('sha256', $message, $base64Secret, true);
        
        // ensure base64 encoded (URL-safe)
        return strtr(base64_encode($hash), '+/', '-_');
    }
}
