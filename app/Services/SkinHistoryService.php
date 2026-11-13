<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Skin;
use App\Models\User;
use App\Models\UserSkinHistory;

class SkinHistoryService
{
    public function recordAcquisition(
        User $user,
        Skin $skin,
        string $source,
        string $status = 'inventory',
        ?int $inventoryItemId = null,
        ?int $liveDropId = null,
        ?string $caseName = null,
    ): UserSkinHistory {
        return UserSkinHistory::create([
            'user_id' => $user->id,
            'skin_id' => $skin->id,
            'inventory_item_id' => $inventoryItemId,
            'live_drop_id' => $liveDropId,
            'case_name' => $caseName,
            'weapon' => $skin->weapon,
            'skin_name' => $skin->name,
            'skin_price' => $skin->price,
            'source' => $source,
            'status' => $status,
        ]);
    }

    public function markByInventoryItem(int $inventoryItemId, string $status): void
    {
        UserSkinHistory::query()
            ->where('inventory_item_id', $inventoryItemId)
            ->where('status', 'inventory')
            ->update(['status' => $status]);
    }

    public function markManyByInventoryItems(array $inventoryItemIds, string $status): void
    {
        if (empty($inventoryItemIds)) {
            return;
        }

        UserSkinHistory::query()
            ->whereIn('inventory_item_id', $inventoryItemIds)
            ->where('status', 'inventory')
            ->update(['status' => $status]);
    }
}
