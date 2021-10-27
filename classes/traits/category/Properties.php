<?php

namespace OFFLINE\Mall\Classes\Traits\Category;

use Cache;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\Category;

trait Properties
{
    private $inheritedPropertyGroupsCache;

    public function getInheritedPropertyGroupsAttribute()
    {
        return $this->inherit_property_groups ? $this->getInheritedPropertyGroups() : $this->property_groups;
    }

    /**
     * Returns the property groups of the first parent
     * that does not inherit any.
     */
    public function getInheritedPropertyGroups()
    {
        if ($this->inheritedPropertyGroupsCache) {
            return $this->inheritedPropertyGroupsCache;
        }

        $groups = $this->getParents()->first(function (Category $category) {
            return ! $category->inherit_property_groups;
        })->property_groups;

        if ($groups) {
            $groups->loadMissing('properties');
        }

        return $this->inheritedPropertyGroupsCache = $groups ?? new Collection();
    }

    /**
     * Returns a flattened Collection of all available properties.
     *
     * @return Collection
     */
    public function getPropertiesAttribute()
    {
        return $this->loadMissing('property_groups.properties')->inherited_property_groups->map->properties->flatten();
    }

    /**
     * Return all property ids that are in an array of group ids.
     */
    protected function getPropertiesInGroups(array $groupIds): Collection
    {
        return \DB::table('offline_mall_property_property_group')
                  ->where('property_group_id', $groupIds)
                  ->get(['property_id'])
                  ->pluck('property_id')
                  ->values();
    }
}
