<?php

declare(strict_types=1);

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
        if (property_exists($this, $name)) {
            return $this->$name = $this->page[$name] = $value;
        }
    }
}
