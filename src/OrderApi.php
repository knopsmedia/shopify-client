<?php declare(strict_types=1);

namespace Knops\ShopifyClient;

final class OrderApi extends AbstractServiceEndpoint
{
    public function findUnfulfilledOrders()
    {
        $response = $this->shopifyApi->request('GET', '/orders.json', query: [
            'status' => 'open',
            'financial_status' => 'paid',
            'fulfillment_status' => 'unfulfilled',
        ]);

        return $response->body->orders;
    }
}