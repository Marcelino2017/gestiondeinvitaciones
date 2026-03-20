<?php

namespace App\Interfaces;

interface BaseRepositoryInterface
{
    public function all(
        $page = 1,
        $perPage = 10,
        $search = null,
        array $columns = ['*'],
        $orderBy = '',
        $stateColumn = null,
        $stateValue = 'active'
    );
    public function find($id);
    public function create(array $data);
    public function update(array $data, $id);
    public function delete($id);
}
