<?php

namespace OFFLINE\Mall\Classes\Jobs;

use OFFLINE\Mall\Models\Category;
use Illuminate\Contracts\Queue\Job;
use OFFLINE\Mall\Models\UniquePropertyValue;

class UpdateUniquePropertyForCategory
{
    public function fire(Job $job, $data)
    {
        $category = Category::find($data['id']);

        UniquePropertyValue::resetForCategory($category);

        $job->delete();
    }
}
