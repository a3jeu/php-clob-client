<?php

namespace Polymarket\ClobClient\OrderBuilder;

use Polymarket\ClobClient\Config;
use Polymarket\ClobClient\Signing\OrderSigner;
use Polymarket\ClobClient\Types\CreateOrderOptions;
use Polymarket\ClobClient\Types\UserMarketOrder;
use Polymarket\ClobClient\Types\UserOrder;

class OrderBuilder
{
    private string $funderAddress;

    public function __construct(
        private string $privateKey,
        private string $address,
        private int $chainId,
        private int $signatureType,
        ?string $funderAddress = null
    ) {
        $this->funderAddress = $funderAddress ?? $address;
    }

    public function buildOrder(UserOrder $userOrder, CreateOrderOptions $options): array
    {
        $orderData = OrderUtils::buildOrderCreationArgs(
            OrderUtils::normalizeAddress($this->address),
            OrderUtils::normalizeAddress($this->funderAddress),
            $this->signatureType,
            $userOrder,
            OrderUtils::ROUNDING_CONFIG[$options->tickSize]
        );

        $contractConfig = Config::getContractConfig($this->chainId);
        $exchangeContract = $options->negRisk
            ? $contractConfig['negRiskExchange']
            : $contractConfig['exchange'];

        return $this->buildSignedOrder($orderData, $exchangeContract);
    }

    public function buildMarketOrder(UserMarketOrder $userMarketOrder, CreateOrderOptions $options): array
    {
        $orderData = OrderUtils::buildMarketOrderCreationArgs(
            OrderUtils::normalizeAddress($this->address),
            OrderUtils::normalizeAddress($this->funderAddress),
            $this->signatureType,
            $userMarketOrder,
            OrderUtils::ROUNDING_CONFIG[$options->tickSize]
        );

        $contractConfig = Config::getContractConfig($this->chainId);
        $exchangeContract = $options->negRisk
            ? $contractConfig['negRiskExchange']
            : $contractConfig['exchange'];

        return $this->buildSignedOrder($orderData, $exchangeContract);
    }

    private function buildSignedOrder(array $orderData, string $exchangeContract): array
    {
        $order = $orderData;
        $order['salt'] = random_int(0, (2 ** 32) - 1);

        $signature = OrderSigner::signOrder($order, $this->chainId, $exchangeContract, $this->privateKey);
        $order['signature'] = $signature;

        return $order;
    }
}
