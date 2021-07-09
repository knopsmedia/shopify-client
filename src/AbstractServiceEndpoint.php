<?php declare(strict_types=1);

namespace Knops\ShopifyClient;

abstract class AbstractServiceEndpoint
{
    public function __construct(
        protected ApiClient $shopifyApi,
    ) {}
}