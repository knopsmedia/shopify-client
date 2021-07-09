<?php declare(strict_types=1);

namespace Knops\ShopifyClient;

final class InventoryLevelApi extends AbstractServiceEndpoint
{
    public function getInventoryLevels(array $inventoryItemIds): array
    {
        $inventoryLevels = $this->shopifyApi->request('GET', '/inventory_levels.json', query: [
            'inventory_item_ids' => implode(',', $inventoryItemIds),
        ]);

        return $inventoryLevels->body->inventory_levels;
    }

    public function updateStock(int $inventoryItemId, int $locationId, int $quantityAvailable)
    {
        $this->shopifyApi->request('POST', '/inventory_levels/set.json', [
            'inventory_item_id' => $inventoryItemId,
            'location_id'       => $locationId,
            'available'         => $quantityAvailable,
        ]);
    }
}