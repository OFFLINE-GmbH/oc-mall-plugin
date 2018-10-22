<?php


namespace OFFLINE\Mall\Classes\Index;

interface ProductIndex
{
    public function insert(ProductEntry $data);
    public function update($id, ProductEntry $data);
    public function delete($id);
    public function fetch(array $filters, $perPage = 9);
}
