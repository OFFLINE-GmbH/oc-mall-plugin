<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Queries;

use OFFLINE\Mall\Classes\Queries\UniquePropertyValuesInCategoriesQuery;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyGroup;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\UniquePropertyValue;
use OFFLINE\Mall\Tests\PluginTestCase;

class UniquePropertyValuesInCategoriesQueryTest extends PluginTestCase
{
    public function test_query_returns_valid_properties(): void
    {
        $category1 = new Category();
        $category1->name = 'Category 1';
        $category1->slug = 'category-1';
        $category1->save();

        $propertyGroup1 = new PropertyGroup();
        $propertyGroup1->name = 'Property group 1';
        $propertyGroup1->slug = 'property-group-1';
        $propertyGroup1->save();

        $property1 = new Property();
        $property1->name = 'Property 1';
        $property1->slug = 'property-1';
        $property1->type = 'dropdown';
        $property1->save();

        $property2 = new Property();
        $property2->name = 'Property 2';
        $property2->slug = 'property-2';
        $property2->type = 'dropdown';
        $property2->save();

        $propertyGroup1->categories()->add($category1);
        $propertyGroup1->properties()->add($property1);
        $propertyGroup1->properties()->add($property2);

        $category2 = new Category();
        $category2->name = 'Category 2';
        $category2->slug = 'category-2';
        $category2->save();

        $propertyGroup2 = new PropertyGroup();
        $propertyGroup2->name = 'Property group 2';
        $propertyGroup2->slug = 'property-group-2';
        $propertyGroup2->save();

        $property3 = new Property();
        $property3->name = 'Property 3';
        $property3->slug = 'property-3';
        $property3->type = 'dropdown';
        $property3->save();

        $property4 = new Property();
        $property4->name = 'Property 4';
        $property4->slug = 'property-4';
        $property4->type = 'dropdown';
        $property4->save();

        $propertyGroup2->categories()->add($category2);
        $propertyGroup2->properties()->add($property1);
        $propertyGroup2->properties()->add($property3);
        $propertyGroup2->properties()->add($property4);

        $products = Product::all();
        $products[0]->categories()->add($category1);
        $products[1]->categories()->add($category2);

        $propertyValue1 = new PropertyValue();
        $propertyValue1->product_id = $products[0]->id;
        $propertyValue1->property_id = $property1->id;
        $propertyValue1->value = 'Property 1 value for product 1';
        $propertyValue1->save();

        $propertyValue2 = new PropertyValue();
        $propertyValue2->product_id = $products[0]->id;
        $propertyValue2->property_id = $property2->id;
        $propertyValue2->value = 'Property 2 value for product 1';
        $propertyValue2->save();

        $propertyValue3 = new PropertyValue();
        $propertyValue3->product_id = $products[0]->id;
        $propertyValue3->property_id = $property2->id;
        $propertyValue3->value = 'Property 2 value for product 1';
        $propertyValue3->save();

        $propertyValue4 = new PropertyValue();
        $propertyValue4->product_id = $products[1]->id;
        $propertyValue4->property_id = $property3->id;
        $propertyValue4->value = 'Property 3 value for product 2';
        $propertyValue4->save();

        $propertyValue5 = new PropertyValue();
        $propertyValue5->product_id = $products[1]->id;
        $propertyValue5->property_id = $property4->id;
        $propertyValue5->value = 'Property 4 value for product 2';
        $propertyValue5->save();

        // The same value for different product
        $propertyValue6 = new PropertyValue();
        $propertyValue6->product_id = $products[1]->id;
        $propertyValue6->property_id = $property1->id;
        $propertyValue6->value = 'Property 1 value for product 1';
        $propertyValue6->save();

        // There are two filled properties for category-1
        $categories = Category::where('slug', 'category-1')->get();
        $records = (new UniquePropertyValuesInCategoriesQuery($categories))->query()->get();
        $this->assertEquals(2, $records->count());
        $this->assertEquals($records[0]->value, 'Property 1 value for product 1');
        $this->assertEquals($records[1]->value, 'Property 2 value for product 1');

        $records = UniquePropertyValue::hydratePropertyValuesForCategories($categories);
        $this->assertEquals(2, $records->count());
        $this->assertEquals($records[0]->value, 'Property 1 value for product 1');
        $this->assertEquals($records[1]->value, 'Property 2 value for product 1');

        // There are two filled properties for category-2 (and three attached)
        $categories = Category::where('slug', 'category-2')->get();
        $records = (new UniquePropertyValuesInCategoriesQuery($categories))->query()->get();
        $this->assertEquals(3, $records->count());
        $this->assertEquals($records[0]->value, 'Property 1 value for product 1');
        $this->assertEquals($records[1]->value, 'Property 3 value for product 2');
        $this->assertEquals($records[2]->value, 'Property 4 value for product 2');

        $records = UniquePropertyValue::hydratePropertyValuesForCategories($categories);
        $this->assertEquals(3, $records->count());
        $this->assertEquals($records[0]->value, 'Property 1 value for product 1');
        $this->assertEquals($records[1]->value, 'Property 3 value for product 2');
        $this->assertEquals($records[2]->value, 'Property 4 value for product 2');

        // There are 4 unique properties' values for both categories
        // One is duplicated and the second has the same value for a different product
        $categories = Category::where('slug', 'like', 'category%')->get();
        $records = (new UniquePropertyValuesInCategoriesQuery($categories))->query()->get();
        $this->assertEquals(4, $records->count());
        $this->assertEquals($records[0]->value, 'Property 1 value for product 1');
        $this->assertEquals($records[1]->value, 'Property 2 value for product 1');
        $this->assertEquals($records[2]->value, 'Property 3 value for product 2');
        $this->assertEquals($records[3]->value, 'Property 4 value for product 2');

        $records = UniquePropertyValue::hydratePropertyValuesForCategories($categories);
        $this->assertEquals(4, $records->count());
        $this->assertEquals($records[0]->value, 'Property 1 value for product 1');
        $this->assertEquals($records[1]->value, 'Property 2 value for product 1');
        $this->assertEquals($records[2]->value, 'Property 3 value for product 2');
        $this->assertEquals($records[3]->value, 'Property 4 value for product 2');
    }
}
