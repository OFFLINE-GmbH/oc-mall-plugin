<?php

namespace OFFLINE\Mall\Classes\Jobs;

use Cache;
use Illuminate\Contracts\Queue\Job;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\UniquePropertyValue;

class UpdateUniquePropertyForCategory
{
    public function fire(Job $job, $data)
    {
        $category = Category::find($data['id']);

        Cache::forget(UniquePropertyValue::getCacheKeyForCategory($category));

        UniquePropertyValue::resetForCategory($category);

        $job->delete();
    }
}
