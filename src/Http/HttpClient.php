<?php

namespace Polymarket\ClobClient\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HttpClient
{
    private Client $client;

    public function __construct(private string $baseUrl)
    {
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'timeout' => 30.0,
            'verify' => true,
        ]);
    }

    /**
     * Perform a GET request
     */
    public function get(string $endpoint, array $headers = [], array $params = []): array
    {
        try {
            $options = ['headers' => $headers];
            if (!empty($params)) {
                $options['query'] = $params;
            }

            $response = $this->client->get($endpoint, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('GET request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Perform a POST request
     */
    public function post(string $endpoint, array $headers = [], array $body = []): array
    {
        try {
            $options = [
                'headers' => array_merge($headers, ['Content-Type' => 'application/json']),
                'json' => $body,
            ];

            $response = $this->client->post($endpoint, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('POST request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Perform a DELETE request
     */
    public function delete(string $endpoint, array $headers = [], array $body = []): array
    {
        try {
            $options = ['headers' => $headers];
            if (!empty($body)) {
                $options['json'] = $body;
            }

            $response = $this->client->delete($endpoint, $options);
            $content = $response->getBody()->getContents();
            return $content ? json_decode($content, true) : [];
        } catch (GuzzleException $e) {
            throw new \RuntimeException('DELETE request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Perform a PUT request
     */
    public function put(string $endpoint, array $headers = [], array $body = []): array
    {
        try {
            $options = [
                'headers' => array_merge($headers, ['Content-Type' => 'application/json']),
                'json' => $body,
            ];

            $response = $this->client->put($endpoint, $options);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('PUT request failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
