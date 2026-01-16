<?php

namespace Polymarket\ClobClient\Types;

class CreateOrderOptions
{
    public function __construct(
        public string $tickSize,
        public bool $negRisk = false
    ) {}

    public function toArray(): array
    {
        return [
            'tickSize' => $this->tickSize,
            'negRisk' => $this->negRisk,
        ];
    }
}
