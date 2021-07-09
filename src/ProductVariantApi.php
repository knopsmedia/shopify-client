<?php declare(strict_types=1);

namespace Knops\ShopifyClient;

final class ProductVariantApi extends AbstractServiceEndpoint
{
    public function create(int $productId, array $data): bool
    {
        try {
            $this->shopifyApi->request('POST', sprintf('/products/%d/variants.json', $productId), ['variant' => $data]);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function update(int $id, array $data): bool
    {
        try {
            $this->shopifyApi->request('PUT', sprintf('/variants/%d.json', $id), ['variant' => $data]);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}