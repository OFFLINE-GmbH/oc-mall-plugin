<?php


namespace OFFLINE\Mall\Classes\Index;

interface Entry
{
    public function data(): array;

    public function withData(array $data): Entry;
}
