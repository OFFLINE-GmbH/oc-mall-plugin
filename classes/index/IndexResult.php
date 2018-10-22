<?php

namespace OFFLINE\Mall\Classes\Index;

class IndexResult
{
    /**
     * An array of all matching ids.
     *
     * @var array<integer>
     */
    public $ids = [];
    /**
     * @var int
     */
    public $totalCount = 0;

    public function __construct($ids, $totalCount)
    {
        $this->ids        = $ids;
        $this->totalCount = $totalCount;
    }
}
