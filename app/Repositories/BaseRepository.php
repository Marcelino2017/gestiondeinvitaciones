<?php

namespace App\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class BaseRepository implements BaseRepositoryInterface
{
    protected $model;

    /**
     * Create a new class instance.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(
        $page = 1,
        $perPage = 10,
        $search = null,
        array $columns = ['*'],
        $orderBy = '',
        $stateColumn = null,
        $stateValue = 'active'
    ){
        $table = $this->model->getTable();
        $query = $this->model->newQuery();

        if ($stateColumn && Schema::hasColumn($table, $stateColumn)) {
            $query->where($stateColumn, $stateValue);
        }

        if ($search && Schema::hasColumn($table, 'name')) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $orderByColumn = $orderBy !== ''
            ? $orderBy
            : (Schema::hasColumn($table, 'created_at') ? 'created_at' : $this->model->getKeyName());

        if (! Schema::hasColumn($table, $orderByColumn)) {
            $orderByColumn = $this->model->getKeyName();
        }

        return $query
            ->orderBy($orderByColumn, 'desc')
            ->paginate($perPage, $columns, 'page', $page);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(array $data, $id)
    {
        $query = $this->model->find($id);
        return $query->update($data);
    }

    public function delete($id)
    {
        return $this->model->find($id)->delete();
    }
}
