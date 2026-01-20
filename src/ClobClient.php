<?php

namespace Polymarket\ClobClient;

use Polymarket\ClobClient\Headers\HeaderBuilder;
use Polymarket\ClobClient\Http\HttpClient;
use Polymarket\ClobClient\Types\ApiKeyCreds;
use Polymarket\ClobClient\Types\CreateOrderOptions;
use Polymarket\ClobClient\Types\OrderType;
use Polymarket\ClobClient\Types\Side;
use Polymarket\ClobClient\Types\UserOrder;

class ClobClient
{
    private HttpClient $httpClient;
    private ?ApiKeyCreds $creds = null;
    private int $signatureType;
    private ?string $funder = null;

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
    public function postOrder(array $signedOrder): array
    {
        $this->ensureL2Auth();

        $body = json_encode($signedOrder);
        $headers = HeaderBuilder::createL2Headers(
            $this->address,
            $this->creds,
            'POST',
            Endpoints::POST_ORDER,
            $body
        );

        return $this->httpClient->post(Endpoints::POST_ORDER, $headers, $signedOrder);
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
}
