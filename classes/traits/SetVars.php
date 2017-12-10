<?php

namespace OFFLINE\Mall\Classes\Traits;

trait SetVars
{
    protected function setVar($name, $value)
    {
        return $this->$name = $this->page[$name] = $value;
    }
}
