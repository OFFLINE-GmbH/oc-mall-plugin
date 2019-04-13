<?php

namespace OFFLINE\Mall\Classes\CategoryFilter\SortOrder;

use OFFLINE\Mall\Models\Property as PropertyModel;

class Property extends SortOrder
{
    public $property;
    public $direction;

    public function __construct($id = null, $direction = 'desc')
    {
        $this->property = PropertyModel::findOrFail($id);
        $this->direction = $direction;

        parent::__construct();
    }

    public function key(): string
    {
        return 'property.'.$this->property->id;
    }

    public function property(): string
    {
        return 'property_values.' . $this->property->id.'[0]';
    }

    public function direction(): string
    {
        return $this->direction;
    }

    public static function dynamicOptions()
    {
        $desc = PropertyModel::all()->map(function(PropertyModel $data) {
            return new static($data->id, 'desc');
        });

        $asc = PropertyModel::all()->map(function(PropertyModel $data) {
            return new static($data->id, 'asc');
        });

        return $desc->merge($asc)->toArray();
    }

    /**
     * The translated label of this option.
     *
     * @return string
     */
    public function label(): string
    {
        return $this->property->name.' '.$this->direction;
    }

}
