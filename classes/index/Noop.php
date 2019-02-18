<?php

namespace OFFLINE\Mall\Classes\Index;

use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\CategoryFilter\SortOrder\SortOrder;

class Noop implements Index
{
    public function __construct()
    {

    }

    public function insert(string $index, Entry $entry)
    {

    }

    public function update(string $index, $id, Entry $entry)
    {

    }

    public function delete(string $index, $id)
    {

    }

    public function create(string $index)
    {

    }

    public function drop(string $index)
    {

    }

    public function fetch(string $index, Collection $filters, SortOrder $order, int $perPage, int $forPage): IndexResult
    {

    }

    protected function search(string $index, Collection $filters, SortOrder $order)
    {

    }
}
