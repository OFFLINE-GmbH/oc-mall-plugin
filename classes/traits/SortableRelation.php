<?php

namespace OFFLINE\Mall\Classes\Traits;

use DB;
use Exception;

trait SortableRelation
{
    public function setRelationOrder($relationName, $itemIds, $itemOrders = null, $sortKey = 'sort_order')
    {
        if ( ! is_array($itemIds)) {
            $itemIds = [$itemIds];
        }

        if ($itemOrders === null) {
            $itemOrders = $itemIds;
        }

        if (count($itemIds) != count($itemOrders)) {
            throw new Exception('Invalid setRelationOrder call - count of itemIds do not match count of itemOrders');
        }

        $relation = $this->{$relationName}();

        foreach ($itemIds as $index => $id) {
            $order    = (int)$itemOrders[$index] ?? $relation->getRelated()->count();
            if (method_exists($relation, 'updateExistingPivot')) {
                $relation->updateExistingPivot($id, [ $sortKey => $order ]);
            } else {
                $record = $relation->getRelated()->find($id);
                $record->{$sortKey} = $order;
                $record->save();
            }
        }
    }
}
