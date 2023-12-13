<?php

namespace App\Common\Traits\Items;

trait SnapshotDeletionTrait
{
    /**
     * Deletes the unused item snapshots.
     *
     * @param int $itemId
     *
     * @return void
     */
    protected function deleteUnusedItemSnapshots($itemId)
    {
        if (!empty($unusedSnapshots = model('item_snapshot')->get_unused_item_snapshot($itemId))) {
            model('item_snapshot')->delete_unused_item_snapshots($itemId, $unusedSnapshots);
        }
    }
}
