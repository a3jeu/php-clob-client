<?php

namespace Polymarket\ClobClient\Signing;

use Elliptic\EC;
use kornrunner\Keccak;

class Eip712Signer
{
    /**
     * Build the canonical Polymarket CLOB EIP712 signature
     */
    public static function buildClobEip712Signature(
        string $privateKey,
        int $chainId,
        int $timestamp,
        int $nonce,
        string $address
    ): string {
        $domain = [
            'name' => SigningConstants::CLOB_DOMAIN_NAME,
            'version' => SigningConstants::CLOB_VERSION,
            'chainId' => $chainId,
        ];

        $types = [
            'EIP712Domain' => [
                ['name' => 'name', 'type' => 'string'],
                ['name' => 'version', 'type' => 'string'],
                ['name' => 'chainId', 'type' => 'uint256'],
            ],
            'ClobAuth' => [
                ['name' => 'address', 'type' => 'address'],
                ['name' => 'timestamp', 'type' => 'string'],
                ['name' => 'nonce', 'type' => 'uint256'],
                ['name' => 'message', 'type' => 'string'],
            ],
        ];

        $message = [
            'address' => $address,
            'timestamp' => (string)$timestamp,
            'nonce' => $nonce,
            'message' => SigningConstants::MSG_TO_SIGN,
        ];

        $domainSeparator = self::hashStruct('EIP712Domain', $domain, $types);
        $messageHash = self::hashStruct('ClobAuth', $message, $types);

        // EIP-712 signature hash: keccak256("\x19\x01" || domainSeparator || messageHash)
        $eip712Hash = Keccak::hash("\x19\x01" . hex2bin($domainSeparator) . hex2bin($messageHash), 256);

        // Sign with private key
        $signature = self::signHash($eip712Hash, $privateKey);

        return $signature;
    }

    private static function hashStruct(string $primaryType, array $data, array $types): string
    {
        $typeHash = self::typeHash($primaryType, $types);
        $encodedData = self::encodeData($primaryType, $data, $types);
        return Keccak::hash(hex2bin($typeHash . $encodedData), 256);
    }

    private static function typeHash(string $primaryType, array $types): string
    {
        return Keccak::hash(self::encodeType($primaryType, $types), 256);
    }

    private static function encodeType(string $primaryType, array $types): string
    {
        $result = $primaryType . '(';
        $params = [];
        foreach ($types[$primaryType] as $field) {
            $params[] = $field['type'] . ' ' . $field['name'];
        }
        $result .= implode(',', $params) . ')';
        return $result;
    }

    private static function encodeData(string $primaryType, array $data, array $types): string
    {
        $encodedValues = [];
        foreach ($types[$primaryType] as $field) {
            $value = $data[$field['name']];
            $encodedValues[] = self::encodeValue($field['type'], $value);
        }
        return implode('', $encodedValues);
    }

    private static function encodeValue(string $type, $value): string
    {
        if ($type === 'string') {
            return Keccak::hash($value, 256);
        } elseif ($type === 'address') {
            return str_pad(str_replace('0x', '', strtolower($value)), 64, '0', STR_PAD_LEFT);
        } elseif (str_starts_with($type, 'uint')) {
            return str_pad(dechex((int)$value), 64, '0', STR_PAD_LEFT);
        }
        return str_pad($value, 64, '0', STR_PAD_LEFT);
    }

    private static function signHash(string $hash, string $privateKey): string
    {
        $ec = new EC('secp256k1');
        
        // Remove '0x' prefix if present
        $privateKey = str_replace('0x', '', $privateKey);
        
        $key = $ec->keyFromPrivate($privateKey, 'hex');
        $signature = $key->sign($hash, ['canonical' => true]);
        
        $r = str_pad($signature->r->toString(16), 64, '0', STR_PAD_LEFT);
        $s = str_pad($signature->s->toString(16), 64, '0', STR_PAD_LEFT);
        $v = dechex($signature->recoveryParam + 27);
        
        return '0x' . $r . $s . $v;
    }
}
