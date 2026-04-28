<?php

namespace Polymarket\ClobClient\Types;

class UserMarketOrder
{
    public function __construct(
        public string $tokenID,
        public float $amount,
        public Side $side,
        public ?float $price = null,
        public ?OrderType $orderType = null,
        public ?string $builderCode = null,
        public ?string $metadata = null
    ) {}

    public function toArray(): array
    {
        $data = [
            'tokenID' => $this->tokenID,
            'amount' => $this->amount,
            'side' => $this->side->value,
        ];

        if ($this->price !== null) {
            $data['price'] = $this->price;
        }
        if ($this->orderType !== null) {
            $data['orderType'] = $this->orderType->value;
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
