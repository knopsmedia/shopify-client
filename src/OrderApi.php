<?php declare(strict_types=1);

namespace Knops\ShopifyClient;

final class OrderApi extends AbstractServiceEndpoint
{
    public function findUnfulfilledOrders(?\DateTimeInterface $after = null)
    {
        $criteria = [
            'status'             => 'open',
            'financial_status'   => 'paid',
            'fulfillment_status' => 'unfulfilled',
        ];

        if ($after !== null) {
            $criteria['created_at_min'] = $after->format(DATE_ATOM);
        }

        $response = $this->shopifyApi->request('GET', '/orders.json', query: $criteria);

        return $response->body->orders;
    }
}