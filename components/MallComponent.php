<?php

namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use OFFLINE\Mall\Classes\Traits\HashIds;

/**
 * This is the base class of all OFFLINE.Mall components.
 */
abstract class MallComponent extends ComponentBase
{
    use HashIds;

    protected function setVar($name, $value)
    {
        return $this->$name = $this->page[$name] = $value;
    }
}