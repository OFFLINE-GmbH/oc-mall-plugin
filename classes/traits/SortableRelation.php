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

        foreach ($itemIds as $index => $id) {
            $order    = $itemOrders[$index];
            $relation = $this->getRelationDefinition($relationName);
            DB::table($relation['table'])
              ->where($relation['key'], $this->id)
              ->where($relation['otherKey'], $id)
              ->update([$sortKey => $order]);
        }
    }
}
