<?php

namespace Polymarket\ClobClient\Types;

class UserMarketOrder
{
    public function __construct(
        public string $tokenID,
        public float $amount,
        public Side $side,
        public ?float $price = null,
        public ?int $feeRateBps = null,
        public ?int $nonce = null,
        public ?string $taker = null,
        public ?OrderType $orderType = null
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
        if ($this->feeRateBps !== null) {
            $data['feeRateBps'] = $this->feeRateBps;
        }
        if ($this->nonce !== null) {
            $data['nonce'] = $this->nonce;
        }
        if ($this->taker !== null) {
            $data['taker'] = $this->taker;
        }
        if ($this->orderType !== null) {
            $data['orderType'] = $this->orderType->value;
        }

        return $data;
    }
}
