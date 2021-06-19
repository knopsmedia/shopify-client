<?php declare(strict_types=1);

namespace Knops\Shopify;

final class ProductApi
{
    private ApiClient $shopifyApi;

    public function __construct(ApiClient $shopifyApi)
    {
        $this->shopifyApi = $shopifyApi;
    }

    public function findOneByHandle(string $handle): ?object
    {
        $all = $this->findAllByHandles([$handle]);

        return empty($all) ? null : $all[0];
    }

    public function findAllByHandles(array $handles): array
    {
        $response = $this->shopifyApi->request('GET', '/products.json', query: [
            'handle' => implode(',', $handles),
        ]);

        return $response->body->products;
    }

    public function create(array $data): bool
    {
        try {
            $this->shopifyApi->request('POST', '/products.json', ['product' => $data]);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function update(int $productId, array $data): bool
    {
        try {
            $this->shopifyApi->request('PUT', sprintf('/products/%d.json', $productId), ['product' => $data]);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}