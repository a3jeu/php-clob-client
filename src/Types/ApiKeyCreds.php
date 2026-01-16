<?php

namespace Polymarket\ClobClient\Types;

class ApiKeyCreds
{
    public function __construct(
        public string $key,
        public string $secret,
        public string $passphrase
    ) {}

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'secret' => $this->secret,
            'passphrase' => $this->passphrase,
        ];
    }
}
