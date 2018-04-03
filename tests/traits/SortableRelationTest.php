<?php namespace OFFLINE\Mall\Tests\Models;

use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Property;
use PluginTestCase;

class SortableRelationTest extends PluginTestCase
{
    public function test_it_handels_initial_sort_order()
    {
        $category       = Category::first();
        $property       = Property::first()->replicate();
        $property->name = 'New Property';

        $category->properties()->save($property);

        // $this->assertEquals(4, $property->categories->first()->pivot->sort_order);
    }

    public function test_it_sets_relation_order()
    {
        $category = Category::first();

        $category->setRelationOrder('properties', [3, 2, 1], range(1, 3));

        $order = $category->fresh('properties')->properties->pluck('pivot.sort_order', 'id');
        $this->assertEquals([1 => 3, 2 => 2, 3 => 1], $order->toArray());
    }
}
