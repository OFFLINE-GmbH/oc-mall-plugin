<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\PropertyGroup;
use OFFLINE\Mall\Tests\PluginTestCase;

class SortableRelationTest extends PluginTestCase
{
    public function test_it_handles_initial_sort_order()
    {
        $category       = Category::first();

        $propertyGroup = new PropertyGroup();
        $propertyGroup->name = 'Testgroup';
        $propertyGroup->save();
        $category->property_groups()->attach($propertyGroup->id);

        $this->markTestIncomplete('Initial order is not yet set properly');

        // $this->assertEquals(4, $category->property_groups->first()->pivot->sort_order);
    }

    public function test_it_sets_relation_order()
    {
        $group =  PropertyGroup::first();

        $group->setRelationOrder('properties', [3, 2, 1], range(1, 3));

        $order = $group->fresh('properties')->properties->pluck('pivot.sort_order', 'id');
        $this->assertEquals([1 => 3, 2 => 2, 3 => 1], $order->toArray());
    }
}
