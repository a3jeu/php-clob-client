<?php

namespace Polymarket\ClobClient\Signing;

use Elliptic\EC;
use kornrunner\Keccak;

class Address
{
    public static function fromPrivateKey(string $privateKey): string
    {
        $normalizedKey = str_replace('0x', '', $privateKey);
        $ec = new EC('secp256k1');
        $key = $ec->keyFromPrivate($normalizedKey, 'hex');

        $publicKey = $key->getPublic(false, 'hex');
        $publicKey = substr($publicKey, 2);

        $hash = Keccak::hash(hex2bin($publicKey), 256);

        return '0x' . substr($hash, -40);
    }
}
