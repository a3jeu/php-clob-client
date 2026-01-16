<?php

namespace Polymarket\ClobClient\Types;

class UserOrder
{
    public function __construct(
        public string $tokenID,
        public float $price,
        public float $size,
        public Side $side,
        public ?int $feeRateBps = null,
        public ?int $nonce = null,
        public ?int $expiration = null,
        public ?string $taker = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'tokenID' => $this->tokenID,
            'price' => $this->price,
            'size' => $this->size,
            'side' => $this->side->value,
        ];

        if ($this->feeRateBps !== null) {
            $data['feeRateBps'] = $this->feeRateBps;
        }
        if ($this->nonce !== null) {
            $data['nonce'] = $this->nonce;
        }
        if ($this->expiration !== null) {
            $data['expiration'] = $this->expiration;
        }
        if ($this->taker !== null) {
            $data['taker'] = $this->taker;
        }

        return $data;
    }
}
