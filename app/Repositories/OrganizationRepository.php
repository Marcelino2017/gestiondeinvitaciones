<?php

namespace App\Repositories;

use App\Interfaces\OrganizationRepositoryInterface;
use App\Models\Organization;
use Illuminate\Support\Collection;

class OrganizationRepository extends BaseRepository implements OrganizationRepositoryInterface
{
    public function __construct(Organization $model)
    {
        parent::__construct($model);
    }


    public function allForUser(int $userId): Collection
    {
        return $this->model
            ->whereHas('users', fn ($query) => $query->where('users.id', $userId))
            ->latest()
            ->get();
    }

    public function findOrFail(int $id): Organization
    {
        return $this->model->findOrFail($id);
    }
}
