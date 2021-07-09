<?php declare(strict_types=1);

namespace Knops\ShopifyClient;

final class ApiClient
{
    private ?object $lastResponse = null;
    private float $lastRequestTime = .0;
    private float $requestDelay = 0.5;

    public function __construct(
        private string $shopUrl,
        private string $apiVersion,
        private string $apiAccessToken,
    ) {}

    public function products(): ProductApi
    {
        return new ProductApi($this);
    }

    public function variants(): ProductVariantApi
    {
        return new ProductVariantApi($this);
    }

    public function inventoryLevels(): InventoryLevelApi
    {
        return new InventoryLevelApi($this);
    }

    public function orders(): OrderApi
    {
        return new OrderApi($this);
    }

    public function request(string $method, string $path, array $body = [], array $query = [], array $headers = []): object
    {
        if ($this->lastResponse !== null && $this->lastResponse->meta['rate-limit'] === '40/40') {
            throw new \Exception('Maximum number of requests allowed reached!');
        }

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'X-Shopify-Access-Token: ' . $this->apiAccessToken;

        $url = sprintf(
            '%s/admin/api/%s%s%s',
            $this->shopUrl, $this->apiVersion, $path, $query ? '?' . http_build_query($query) : ''
        );

        $this->delayNextRequestIfNecessary();

        $responseBody = @file_get_contents($url, false, stream_context_create([
            'http' => [
                'method'        => $method,
                'header'        => $headers,
                'content'       => $body ? json_encode($body) : null,
                'ignore_errors' => true,
            ],
        ]));

        if (false === $responseBody) {
            $error = error_get_last();
            throw new \Exception($error['message'], $error['type']);
        }

        return $this->createResponse($responseBody, $http_response_header, $method);
    }

    private function delayNextRequestIfNecessary()
    {
        $microseconds = microtime(true);
        $delta = $microseconds - $this->lastRequestTime;

        if ($delta < $this->requestDelay) {
            // 1 sec = 1.000.000 msec
            usleep((int)(($this->requestDelay - $delta) * 1000000));
        }

        $this->lastRequestTime = microtime(true);
    }

    private function parseHttpResponseHeaders(array $http_response_headers, int &$statusCode, string &$reasonPhrase): array
    {
        $headers = [];

        foreach ($http_response_headers as $http_response_header) {
            if (str_starts_with($http_response_header, 'HTTP/1.1')) {
                $statusCode = (int)substr($http_response_header, 9, 3);
                $reasonPhrase = substr($http_response_header, 13);

                continue;
            }

            [$header, $value] = array_map('trim', explode(':', $http_response_header, 2));

            if (str_starts_with($http_response_header, 'HTTP_')) {
                $headers[$header] = $value;

                continue;
            }

            $headers[strtolower($header)] = $value;
        }

        return $headers;
    }

    private function createResponse(string $responseBody, array $responseHeaders, string $requestMethod): object
    {
        $statusCode = 200;
        $reasonPhrase = '';
        $responseHeaders = $this->parseHttpResponseHeaders($responseHeaders, $statusCode, $reasonPhrase);

        $this->lastResponse = (object)[
            'code' => $statusCode,
            'headers' => $responseHeaders,
            'body' => json_decode($responseBody),
            'meta' => [
                'rate-limit' => $responseHeaders['x-shopify-shop-api-call-limit']
            ],
        ];

        return $this->lastResponse;
    }
}