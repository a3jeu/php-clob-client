<?php

namespace Polymarket\ClobClient\Types;

class UserOrder
{
    public function __construct(
        public string $tokenID,
        public float $price,
        public float $size,
        public Side $side,
        public ?int $expiration = null,
        public ?string $builderCode = null,
        public ?string $metadata = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'tokenID' => $this->tokenID,
            'price' => $this->price,
            'size' => $this->size,
            'side' => $this->side->value,
        ];

        if ($this->expiration !== null) {
            $data['expiration'] = $this->expiration;
        }
        if ($this->builderCode !== null) {
            $data['builderCode'] = $this->builderCode;
        }
        if ($this->metadata !== null) {
            $data['metadata'] = $this->metadata;
        }

        return $data;
    }
}
