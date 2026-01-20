<?php

namespace Polymarket\ClobClient\OrderBuilder;

use Polymarket\ClobClient\Constants;
use Polymarket\ClobClient\Config;
use Polymarket\ClobClient\Types\OrderType;
use Polymarket\ClobClient\Types\Side;
use Polymarket\ClobClient\Types\UserMarketOrder;
use Polymarket\ClobClient\Types\UserOrder;

class OrderUtils
{
    public const ROUNDING_CONFIG = [
        '0.1' => [
            'price' => 1,
            'size' => 2,
            'amount' => 3,
        ],
        '0.01' => [
            'price' => 2,
            'size' => 2,
            'amount' => 4,
        ],
        '0.001' => [
            'price' => 3,
            'size' => 2,
            'amount' => 5,
        ],
        '0.0001' => [
            'price' => 4,
            'size' => 2,
            'amount' => 6,
        ],
    ];

    public static function normalizeAddress(string $address): string
    {
        $normalized = strtolower($address);
        if (!str_starts_with($normalized, '0x')) {
            $normalized = '0x' . $normalized;
        }
        return $normalized;
    }

    public static function roundNormal(float $num, int $decimals): float
    {
        if (self::decimalPlaces($num) <= $decimals) {
            return $num;
        }
        return round($num, $decimals, PHP_ROUND_HALF_UP);
    }

    public static function roundDown(float $num, int $decimals): float
    {
        if (self::decimalPlaces($num) <= $decimals) {
            return $num;
        }
        $factor = 10 ** $decimals;
        return floor($num * $factor) / $factor;
    }

    public static function roundUp(float $num, int $decimals): float
    {
        if (self::decimalPlaces($num) <= $decimals) {
            return $num;
        }
        $factor = 10 ** $decimals;
        return ceil($num * $factor) / $factor;
    }

    public static function decimalPlaces(float $num): int
    {
        if (fmod($num, 1.0) === 0.0) {
            return 0;
        }
        $stringValue = rtrim(sprintf('%.12f', $num), '0');
        $parts = explode('.', $stringValue);
        return count($parts) === 2 ? strlen($parts[1]) : 0;
    }

    public static function isTickSizeSmaller(string $a, string $b): bool
    {
        return (float)$a < (float)$b;
    }

    public static function priceValid(float $price, string $tickSize): bool
    {
        $tick = (float)$tickSize;
        return $price >= $tick && $price <= 1 - $tick;
    }

    public static function getOrderRawAmounts(
        Side $side,
        float $size,
        float $price,
        array $roundConfig
    ): array {
        $rawPrice = self::roundNormal($price, $roundConfig['price']);

        if ($side === Side::BUY) {
            $rawTakerAmt = self::roundDown($size, $roundConfig['size']);

            $rawMakerAmt = $rawTakerAmt * $rawPrice;
            if (self::decimalPlaces($rawMakerAmt) > $roundConfig['amount']) {
                $rawMakerAmt = self::roundUp($rawMakerAmt, $roundConfig['amount'] + 4);
                if (self::decimalPlaces($rawMakerAmt) > $roundConfig['amount']) {
                    $rawMakerAmt = self::roundDown($rawMakerAmt, $roundConfig['amount']);
                }
            }

            return [
                'side' => 0,
                'rawMakerAmt' => $rawMakerAmt,
                'rawTakerAmt' => $rawTakerAmt,
            ];
        }

        $rawMakerAmt = self::roundDown($size, $roundConfig['size']);
        $rawTakerAmt = $rawMakerAmt * $rawPrice;
        if (self::decimalPlaces($rawTakerAmt) > $roundConfig['amount']) {
            $rawTakerAmt = self::roundUp($rawTakerAmt, $roundConfig['amount'] + 4);
            if (self::decimalPlaces($rawTakerAmt) > $roundConfig['amount']) {
                $rawTakerAmt = self::roundDown($rawTakerAmt, $roundConfig['amount']);
            }
        }

        return [
            'side' => 1,
            'rawMakerAmt' => $rawMakerAmt,
            'rawTakerAmt' => $rawTakerAmt,
        ];
    }

    public static function getMarketOrderRawAmounts(
        Side $side,
        float $amount,
        float $price,
        array $roundConfig
    ): array {
        $rawPrice = self::roundDown($price, $roundConfig['price']);

        if ($side === Side::BUY) {
            $rawMakerAmt = self::roundDown($amount, $roundConfig['size']);
            $rawTakerAmt = $rawMakerAmt / $rawPrice;
            if (self::decimalPlaces($rawTakerAmt) > $roundConfig['amount']) {
                $rawTakerAmt = self::roundUp($rawTakerAmt, $roundConfig['amount'] + 4);
                if (self::decimalPlaces($rawTakerAmt) > $roundConfig['amount']) {
                    $rawTakerAmt = self::roundDown($rawTakerAmt, $roundConfig['amount']);
                }
            }

            return [
                'side' => 0,
                'rawMakerAmt' => $rawMakerAmt,
                'rawTakerAmt' => $rawTakerAmt,
            ];
        }

        $rawMakerAmt = self::roundDown($amount, $roundConfig['size']);
        $rawTakerAmt = $rawMakerAmt * $rawPrice;
        if (self::decimalPlaces($rawTakerAmt) > $roundConfig['amount']) {
            $rawTakerAmt = self::roundUp($rawTakerAmt, $roundConfig['amount'] + 4);
            if (self::decimalPlaces($rawTakerAmt) > $roundConfig['amount']) {
                $rawTakerAmt = self::roundDown($rawTakerAmt, $roundConfig['amount']);
            }
        }

        return [
            'side' => 1,
            'rawMakerAmt' => $rawMakerAmt,
            'rawTakerAmt' => $rawTakerAmt,
        ];
    }

    public static function buildOrderCreationArgs(
        string $signer,
        string $maker,
        int $signatureType,
        UserOrder $userOrder,
        array $roundConfig
    ): array {
        $amounts = self::getOrderRawAmounts($userOrder->side, $userOrder->size, $userOrder->price, $roundConfig);

        $makerAmount = self::parseUnits((string)$amounts['rawMakerAmt'], Config::COLLATERAL_TOKEN_DECIMALS);
        $takerAmount = self::parseUnits((string)$amounts['rawTakerAmt'], Config::COLLATERAL_TOKEN_DECIMALS);

        $taker = $userOrder->taker ?: Constants::ZERO_ADDRESS;
        $feeRateBps = $userOrder->feeRateBps ?? 0;
        $nonce = $userOrder->nonce ?? 0;
        $expiration = $userOrder->expiration ?? 0;

        return [
            'maker' => $maker,
            'taker' => $taker,
            'tokenId' => $userOrder->tokenID,
            'makerAmount' => $makerAmount,
            'takerAmount' => $takerAmount,
            'side' => $amounts['side'],
            'feeRateBps' => (string)$feeRateBps,
            'nonce' => (string)$nonce,
            'signer' => $signer,
            'expiration' => (string)$expiration,
            'signatureType' => $signatureType,
        ];
    }

    public static function buildMarketOrderCreationArgs(
        string $signer,
        string $maker,
        int $signatureType,
        UserMarketOrder $userMarketOrder,
        array $roundConfig
    ): array {
        $price = $userMarketOrder->price ?? 1.0;
        $amounts = self::getMarketOrderRawAmounts($userMarketOrder->side, $userMarketOrder->amount, $price, $roundConfig);

        $makerAmount = self::parseUnits((string)$amounts['rawMakerAmt'], Config::COLLATERAL_TOKEN_DECIMALS);
        $takerAmount = self::parseUnits((string)$amounts['rawTakerAmt'], Config::COLLATERAL_TOKEN_DECIMALS);

        $taker = $userMarketOrder->taker ?: Constants::ZERO_ADDRESS;
        $feeRateBps = $userMarketOrder->feeRateBps ?? 0;
        $nonce = $userMarketOrder->nonce ?? 0;

        return [
            'maker' => $maker,
            'taker' => $taker,
            'tokenId' => $userMarketOrder->tokenID,
            'makerAmount' => $makerAmount,
            'takerAmount' => $takerAmount,
            'side' => $amounts['side'],
            'feeRateBps' => (string)$feeRateBps,
            'nonce' => (string)$nonce,
            'signer' => $signer,
            'expiration' => '0',
            'signatureType' => $signatureType,
        ];
    }

    public static function orderToPayload(
        array $order,
        string $owner,
        OrderType $orderType,
        bool $deferExec = false,
        ?bool $postOnly = null
    ): array {
        if ($postOnly === true && !in_array($orderType, [OrderType::GTC, OrderType::GTD], true)) {
            throw new \InvalidArgumentException('postOnly is only supported for GTC and GTD orders');
        }

        $side = ($order['side'] ?? 0) === 0 ? Side::BUY->value : Side::SELL->value;

        $payload = [
            'deferExec' => $deferExec,
            'order' => [
                'salt' => (int)$order['salt'],
                'maker' => $order['maker'],
                'signer' => $order['signer'],
                'taker' => $order['taker'],
                'tokenId' => $order['tokenId'],
                'makerAmount' => $order['makerAmount'],
                'takerAmount' => $order['takerAmount'],
                'side' => $side,
                'expiration' => (string)$order['expiration'],
                'nonce' => (string)$order['nonce'],
                'feeRateBps' => (string)$order['feeRateBps'],
                'signatureType' => (int)$order['signatureType'],
                'signature' => $order['signature'],
            ],
            'owner' => $owner,
            'orderType' => $orderType->value,
        ];

        if (is_bool($postOnly)) {
            $payload['postOnly'] = $postOnly;
        }

        return $payload;
    }

    public static function calculateBuyMarketPrice(array $positions, float $amountToMatch, OrderType $orderType): float
    {
        if (count($positions) === 0) {
            throw new \RuntimeException('no match');
        }

        $sum = 0.0;
        for ($i = count($positions) - 1; $i >= 0; $i--) {
            $position = $positions[$i];
            $sum += ((float)$position['size']) * ((float)$position['price']);
            if ($sum >= $amountToMatch) {
                return (float)$position['price'];
            }
        }

        if ($orderType === OrderType::FOK) {
            throw new \RuntimeException('no match');
        }

        return (float)$positions[0]['price'];
    }

    public static function calculateSellMarketPrice(array $positions, float $amountToMatch, OrderType $orderType): float
    {
        if (count($positions) === 0) {
            throw new \RuntimeException('no match');
        }

        $sum = 0.0;
        for ($i = count($positions) - 1; $i >= 0; $i--) {
            $position = $positions[$i];
            $sum += (float)$position['size'];
            if ($sum >= $amountToMatch) {
                return (float)$position['price'];
            }
        }

        if ($orderType === OrderType::FOK) {
            throw new \RuntimeException('no match');
        }

        return (float)$positions[0]['price'];
    }

    public static function parseUnits(string $value, int $decimals): string
    {
        $normalized = self::normalizeDecimalString($value, $decimals);
        $parts = explode('.', $normalized, 2);
        $whole = $parts[0] ?? '0';
        $fraction = $parts[1] ?? '';

        $fraction = str_pad(substr($fraction, 0, $decimals), $decimals, '0', STR_PAD_RIGHT);
        $whole = ltrim($whole, '0');
        if ($whole === '') {
            $whole = '0';
        }

        $result = $whole . $fraction;
        $result = ltrim($result, '0');
        return $result === '' ? '0' : $result;
    }

    private static function normalizeDecimalString(string $value, int $decimals): string
    {
        if (str_contains($value, 'e') || str_contains($value, 'E')) {
            $value = sprintf("%.{$decimals}f", (float)$value);
        }

        if (!str_contains($value, '.')) {
            return $value;
        }

        $value = rtrim($value, '0');
        return rtrim($value, '.');
    }
}
