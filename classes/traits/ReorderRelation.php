<?php

namespace OFFLINE\Mall\Classes\Traits;

use Flash;
use Request;

trait ReorderRelation
{
    public function onReorderRelation($id)
    {
        $model = $this->formFindModelObject($id);
        if ($model and $fieldName = Request::input('fieldName')) {
            $records = Request::input('rcd');
            $sortKey = array_get($model->getRelationDefinition($fieldName), 'sortKey', 'sort_order');

            $model->setRelationOrder($fieldName, $records, range(1, count($records)), $sortKey);

            Flash::success(trans('offline.mall::lang.common.sorting_updated'));

            $this->initRelation($model, $fieldName);
            return $this->relationRefresh($fieldName);
        }
    }

}
