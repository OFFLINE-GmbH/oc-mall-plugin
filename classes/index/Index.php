<?php


namespace OFFLINE\Mall\Classes\Index;


use Illuminate\Support\Collection;

interface Index
{
    public function insert(string $index, Entry $data);

    public function update(string $index, $id, Entry $data);

    public function delete(string $index, $id);

    public function fetch(string $index, Collection $filters, int $perPage, int $forPage): IndexResult;
}