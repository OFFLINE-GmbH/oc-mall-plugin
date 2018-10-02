<?php

namespace OFFLINE\Mall\Classes\Jobs;

use Illuminate\Contracts\Queue\Job;
use OFFLINE\Mall\Models\PropertyValue;

class PropertyRemovalUpdate
{
    public function fire(Job $job, $data)
    {
        if ($job->attempts() > 5) {
            logger()->error('Failed to handle property removal. Please run php artisan mall:reindex manually to update your index');
            $job->delete();
        }

        // Reset any products that were grouped by a removed property.
        \DB::table('offline_mall_products')
           ->whereIn('group_by_property_id', $data['properties'] ?? [])
           ->update([
               'group_by_property_id' => null,
           ]);

        PropertyValue
            ::with(['product', 'variant'])
            ->orderBy('id')
            ->whereIn('property_id', $data['properties'] ?? [])
            ->whereIn('product_id', $data['products'] ?? [])
            ->orWhereIn('variant_id', $data['variants'] ?? [])
            ->chunk(100, function ($values) {
                // Tiggers a re-index via the PropertyValueObserver.
                $values->each->delete();
            });

        $job->delete();
    }
}
