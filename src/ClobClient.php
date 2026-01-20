<?php

namespace Polymarket\ClobClient;

use Polymarket\ClobClient\Headers\HeaderBuilder;
use Polymarket\ClobClient\Http\HttpClient;
use Polymarket\ClobClient\OrderBuilder\OrderBuilder;
use Polymarket\ClobClient\OrderBuilder\OrderUtils;
use Polymarket\ClobClient\Types\ApiKeyCreds;
use Polymarket\ClobClient\Types\CreateOrderOptions;
use Polymarket\ClobClient\Types\OrderType;
use Polymarket\ClobClient\Types\Side;
use Polymarket\ClobClient\Types\UserMarketOrder;
use Polymarket\ClobClient\Types\UserOrder;

class ClobClient
{
    private HttpClient $httpClient;
    private ?ApiKeyCreds $creds = null;
    private int $signatureType;
    private ?string $funder = null;
    private ?OrderBuilder $orderBuilder = null;

    public function __construct(
        private string $host,
        private int $chainId,
        private string $privateKey,
        private string $address,
        ?ApiKeyCreds $creds = null,
        int $signatureType = 0,
        ?string $funder = null
    ) {
        $this->httpClient = new HttpClient($host);
        $this->creds = $creds;
        $this->signatureType = $signatureType;
        $this->funder = $funder ?? $address;
        $this->orderBuilder = $privateKey !== '' ? new OrderBuilder(
            $privateKey,
            $address,
            $chainId,
            $signatureType,
            $this->funder
        ) : null;
    }

    /**
     * Get server time
     */
    public function getServerTime(): array
    {
        return $this->httpClient->get(Endpoints::TIME);
    }

    /**
     * Create API key
     * 
     * WARNING: Credentials cannot be recovered after creation. Store them safely!
     */
    public function createApiKey(?int $nonce = null): ApiKeyCreds
    {
        // Note: Callers should be aware that credentials cannot be recovered
        // Consider logging this warning in your application
        
        $headers = HeaderBuilder::createL1Headers(
            $this->privateKey,
            $this->address,
            $this->chainId,
            $nonce
        );

        $response = $this->httpClient->post(Endpoints::CREATE_API_KEY, $headers);

        return new ApiKeyCreds(
            $response['apiKey'],
            $response['secret'],
            $response['passphrase']
        );
    }

    /**
     * Derive API key
     */
    public function deriveApiKey(?int $nonce = null): ApiKeyCreds
    {
        $headers = HeaderBuilder::createL1Headers(
            $this->privateKey,
            $this->address,
            $this->chainId,
            $nonce
        );

        $response = $this->httpClient->get(Endpoints::DERIVE_API_KEY, $headers);

        return new ApiKeyCreds(
            $response['apiKey'],
            $response['secret'],
            $response['passphrase']
        );
    }

    /**
     * Create or derive API key (recommended)
     */
    public function createOrDeriveApiKey(?int $nonce = null): ApiKeyCreds
    {
        try {
            return $this->deriveApiKey($nonce);
        } catch (\Exception $e) {
            return $this->createApiKey($nonce);
        }
    }

    /**
     * Get API keys
     */
    public function getApiKeys(): array
    {
        $this->ensureL2Auth();

        $headers = HeaderBuilder::createL2Headers(
            $this->address,
            $this->creds,
            'GET',
            Endpoints::GET_API_KEYS
        );

        return $this->httpClient->get(Endpoints::GET_API_KEYS, $headers);
    }

    /**
     * Delete API key
     */
    public function deleteApiKey(): array
    {
        $this->ensureL2Auth();

        $headers = HeaderBuilder::createL2Headers(
            $this->address,
            $this->creds,
            'DELETE',
            Endpoints::DELETE_API_KEY
        );

        return $this->httpClient->delete(Endpoints::DELETE_API_KEY, $headers);
    }

    /**
     * Get order book
     */
    public function getOrderBook(string $tokenId): array
    {
        $params = ['token_id' => $tokenId];
        return $this->httpClient->get(Endpoints::GET_ORDER_BOOK, [], $params);
    }

    /**
     * Get midpoint price
     */
    public function getMidpoint(string $tokenId): array
    {
        $params = ['token_id' => $tokenId];
        return $this->httpClient->get(Endpoints::GET_MIDPOINT, [], $params);
    }

    /**
     * Get price
     */
    public function getPrice(string $tokenId, Side $side): array
    {
        $params = [
            'token_id' => $tokenId,
            'side' => $side->value,
        ];
        return $this->httpClient->get(Endpoints::GET_PRICE, [], $params);
    }

    /**
     * Get last trade price
     */
    public function getLastTradePrice(string $tokenId): array
    {
        $params = ['token_id' => $tokenId];
        return $this->httpClient->get(Endpoints::GET_LAST_TRADE_PRICE, [], $params);
    }

    /**
     * Get tick size
     */
    public function getTickSize(string $tokenId): array
    {
        $params = ['token_id' => $tokenId];
        return $this->httpClient->get(Endpoints::GET_TICK_SIZE, [], $params);
    }

    /**
     * Get negative risk flag
     */
    public function getNegRisk(string $tokenId): array
    {
        $params = ['token_id' => $tokenId];
        return $this->httpClient->get(Endpoints::GET_NEG_RISK, [], $params);
    }

    /**
     * Get markets
     */
    public function getMarkets(?string $nextCursor = null): array
    {
        $params = [];
        if ($nextCursor !== null) {
            $params['next_cursor'] = $nextCursor;
        }
        return $this->httpClient->get(Endpoints::GET_MARKETS, [], $params);
    }

    /**
     * Get market by condition ID
     */
    public function getMarket(string $conditionId): array
    {
        return $this->httpClient->get(Endpoints::GET_MARKET . $conditionId);
    }

    /**
     * Get open orders
     */
    public function getOpenOrders(): array
    {
        $this->ensureL2Auth();

        $headers = HeaderBuilder::createL2Headers(
            $this->address,
            $this->creds,
            'GET',
            Endpoints::GET_OPEN_ORDERS
        );

        return $this->httpClient->get(Endpoints::GET_OPEN_ORDERS, $headers);
    }

    /**
     * Create and sign a limit order
     */
    public function createOrder(UserOrder $userOrder, ?CreateOrderOptions $options = null): array
    {
        $this->ensureL1Auth();

        $tickSize = $this->resolveTickSize($userOrder->tokenID, $options?->tickSize);
        $feeRateBps = $this->resolveFeeRateBps($userOrder->tokenID, $userOrder->feeRateBps);
        $userOrder->feeRateBps = $feeRateBps;

        if (!OrderUtils::priceValid($userOrder->price, $tickSize)) {
            $min = (float)$tickSize;
            $max = 1 - (float)$tickSize;
            throw new \RuntimeException("invalid price ({$userOrder->price}), min: {$min} - max: {$max}");
        }

        $negRisk = $options?->negRisk ?? $this->getNegRisk($userOrder->tokenID);

        return $this->orderBuilder->buildOrder($userOrder, new CreateOrderOptions($tickSize, $negRisk));
    }

    /**
     * Create and sign a market order
     */
    public function createMarketOrder(UserMarketOrder $userMarketOrder, ?CreateOrderOptions $options = null): array
    {
        $this->ensureL1Auth();

        $tickSize = $this->resolveTickSize($userMarketOrder->tokenID, $options?->tickSize);
        $feeRateBps = $this->resolveFeeRateBps($userMarketOrder->tokenID, $userMarketOrder->feeRateBps);
        $userMarketOrder->feeRateBps = $feeRateBps;

        if ($userMarketOrder->price === null) {
            $userMarketOrder->price = $this->calculateMarketPrice(
                $userMarketOrder->tokenID,
                $userMarketOrder->side,
                $userMarketOrder->amount,
                $userMarketOrder->orderType ?? OrderType::FOK
            );
        }

        if (!OrderUtils::priceValid($userMarketOrder->price, $tickSize)) {
            $min = (float)$tickSize;
            $max = 1 - (float)$tickSize;
            throw new \RuntimeException("invalid price ({$userMarketOrder->price}), min: {$min} - max: {$max}");
        }

        $negRisk = $options?->negRisk ?? $this->getNegRisk($userMarketOrder->tokenID);

        return $this->orderBuilder->buildMarketOrder($userMarketOrder, new CreateOrderOptions($tickSize, $negRisk));
    }

    /**
     * Create and post an order
     */
    public function createAndPostOrder(
        UserOrder $userOrder,
        ?CreateOrderOptions $options = null,
        OrderType $orderType = OrderType::GTC,
        bool $deferExec = false,
        bool $postOnly = false
    ): array {
        $order = $this->createOrder($userOrder, $options);
        return $this->postOrder($order, $orderType, $deferExec, $postOnly);
    }

    /**
     * Create and post a market order
     */
    public function createAndPostMarketOrder(
        UserMarketOrder $userMarketOrder,
        ?CreateOrderOptions $options = null,
        OrderType $orderType = OrderType::FOK,
        bool $deferExec = false
    ): array {
        $order = $this->createMarketOrder($userMarketOrder, $options);
        return $this->postOrder($order, $orderType, $deferExec);
    }

    /**
     * Get trades
     */
    public function getTrades(): array
    {
        $this->ensureL2Auth();

        $headers = HeaderBuilder::createL2Headers(
            $this->address,
            $this->creds,
            'GET',
            Endpoints::GET_TRADES
        );

        return $this->httpClient->get(Endpoints::GET_TRADES, $headers);
    }

    /**
     * Post an order
     */
    public function postOrder(
        array $signedOrder,
        OrderType $orderType = OrderType::GTC,
        bool $deferExec = false,
        bool $postOnly = false
    ): array
    {
        $this->ensureL2Auth();

        $payload = OrderUtils::orderToPayload(
            $signedOrder,
            $this->creds?->key ?? '',
            $orderType,
            $deferExec,
            $postOnly
        );
        $body = json_encode($payload);
        $headers = HeaderBuilder::createL2Headers(
            $this->address,
            $this->creds,
            'POST',
            Endpoints::POST_ORDER,
            $body
        );

        return $this->httpClient->post(Endpoints::POST_ORDER, $headers, $payload);
    }

    /**
     * Post multiple orders
     */
    public function postOrders(array $orders, bool $deferExec = false, bool $defaultPostOnly = false): array
    {
        $this->ensureL2Auth();

        $payload = [];
        foreach ($orders as $entry) {
            $order = $entry['order'] ?? null;
            $orderType = $entry['orderType'] ?? OrderType::GTC;
            $postOnly = $entry['postOnly'] ?? $defaultPostOnly;
            if (!$order || !$orderType instanceof OrderType) {
                throw new \InvalidArgumentException('Orders must include order and orderType.');
            }
            $payload[] = OrderUtils::orderToPayload(
                $order,
                $this->creds?->key ?? '',
                $orderType,
                $deferExec,
                $postOnly
            );
        }

        $body = json_encode($payload);
        $headers = HeaderBuilder::createL2Headers(
            $this->address,
            $this->creds,
            'POST',
            Endpoints::POST_ORDERS,
            $body
        );

        return $this->httpClient->post(Endpoints::POST_ORDERS, $headers, $payload);
    }

    /**
     * Cancel multiple orders by hash
     */
    public function cancelOrders(array $ordersHashes): array
    {
        $this->ensureL2Auth();

        $body = json_encode($ordersHashes);
        $headers = HeaderBuilder::createL2Headers(
            $this->address,
            $this->creds,
            'DELETE',
            Endpoints::CANCEL_ORDERS,
            $body
        );

        return $this->httpClient->delete(Endpoints::CANCEL_ORDERS, $headers, $ordersHashes);
    }

    /**
     * Calculate market price for a market order
     */
    public function calculateMarketPrice(
        string $tokenId,
        Side $side,
        float $amount,
        OrderType $orderType = OrderType::FOK
    ): float {
        $book = $this->getOrderBook($tokenId);
        if (!$book) {
            throw new \RuntimeException('no orderbook');
        }

        if ($side === Side::BUY) {
            if (empty($book['asks'])) {
                throw new \RuntimeException('no match');
            }
            return OrderUtils::calculateBuyMarketPrice($book['asks'], $amount, $orderType);
        }

        if (empty($book['bids'])) {
            throw new \RuntimeException('no match');
        }
        return OrderUtils::calculateSellMarketPrice($book['bids'], $amount, $orderType);
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(string $orderId): array
    {
        $this->ensureL2Auth();

        $body = json_encode(['orderID' => $orderId]);
        $headers = HeaderBuilder::createL2Headers(
            $this->address,
            $this->creds,
            'DELETE',
            Endpoints::CANCEL_ORDER,
            $body
        );

        return $this->httpClient->delete(Endpoints::CANCEL_ORDER, $headers, ['orderID' => $orderId]);
    }

    /**
     * Cancel all orders
     */
    public function cancelAll(): array
    {
        $this->ensureL2Auth();

        $headers = HeaderBuilder::createL2Headers(
            $this->address,
            $this->creds,
            'DELETE',
            Endpoints::CANCEL_ALL
        );

        return $this->httpClient->delete(Endpoints::CANCEL_ALL, $headers);
    }

    /**
     * Cancel orders for a market
     */
    public function cancelMarketOrders(string $marketId, ?string $assetId = null): array
    {
        $this->ensureL2Auth();

        $params = ['market' => $marketId];
        if ($assetId !== null) {
            $params['asset_id'] = $assetId;
        }

        $body = json_encode($params);
        $headers = HeaderBuilder::createL2Headers(
            $this->address,
            $this->creds,
            'DELETE',
            Endpoints::CANCEL_MARKET_ORDERS,
            $body
        );

        return $this->httpClient->delete(Endpoints::CANCEL_MARKET_ORDERS, $headers, $params);
    }

    /**
     * Get balance and allowance
     */
    public function getBalanceAllowance(string $assetType, ?string $tokenId = null): array
    {
        $this->ensureL2Auth();

        $params = ['asset_type' => $assetType];
        if ($tokenId !== null) {
            $params['token_id'] = $tokenId;
        }
        if ($this->signatureType !== null) {
            $params['signature_type'] = $this->signatureType;
        }

        $queryString = http_build_query($params);
        $requestPath = Endpoints::GET_BALANCE_ALLOWANCE . '?' . $queryString;

        $headers = HeaderBuilder::createL2Headers(
            $this->address,
            $this->creds,
            'GET',
            Endpoints::GET_BALANCE_ALLOWANCE
        );

        return $this->httpClient->get($requestPath, $headers, $params);
    }

    /**
     * Ensure L2 authentication is available
     */
    private function ensureL2Auth(): void
    {
        if ($this->creds === null) {
            throw new \RuntimeException('L2 authentication not available. API credentials required.');
        }
    }

    /**
     * Ensure L1 authentication is available
     */
    private function ensureL1Auth(): void
    {
        if ($this->orderBuilder === null) {
            throw new \RuntimeException('L1 authentication not available. Private key required.');
        }
    }

    private function resolveTickSize(string $tokenId, ?string $tickSize): string
    {
        $minTickSizeResponse = $this->getTickSize($tokenId);
        $minTickSize = $minTickSizeResponse['minimum_tick_size'] ?? $minTickSizeResponse['minimum_tick_size'] ?? null;
        if ($minTickSize === null) {
            throw new \RuntimeException('Could not resolve tick size for market.');
        }

        if ($tickSize !== null) {
            if (OrderUtils::isTickSizeSmaller($tickSize, (string)$minTickSize)) {
                throw new \RuntimeException(
                    "invalid tick size ({$tickSize}), minimum for the market is {$minTickSize}"
                );
            }
            return $tickSize;
        }

        return (string)$minTickSize;
    }

    private function resolveFeeRateBps(string $tokenId, ?int $userFeeRateBps): int
    {
        $feeRateResponse = $this->getFeeRate($tokenId);
        $marketFeeRateBps = (int)($feeRateResponse['fee_rate_bps'] ?? $feeRateResponse['feeRateBps'] ?? 0);
        if ($marketFeeRateBps > 0 && $userFeeRateBps !== null && $userFeeRateBps !== $marketFeeRateBps) {
            throw new \RuntimeException(
                "invalid user provided fee rate: {$userFeeRateBps}, fee rate for the market must be {$marketFeeRateBps}"
            );
        }

        return $marketFeeRateBps;
    }

    private function getFeeRate(string $tokenId): array
    {
        $params = ['token_id' => $tokenId];
        return $this->httpClient->get(Endpoints::GET_FEE_RATE, [], $params);
    }
}
