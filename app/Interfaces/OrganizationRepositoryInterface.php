<?php

namespace App\Interfaces;

use App\Models\Organization;
use Illuminate\Support\Collection;

interface OrganizationRepositoryInterface extends BaseRepositoryInterface
{
    public function allForUser(int $userId): Collection;

    public function findOrFail(int $id): Organization;
}
