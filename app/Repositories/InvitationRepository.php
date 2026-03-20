<?php

namespace App\Repositories;

use App\Enums\InvitationStatus;
use App\Interfaces\InvitationRepositoryInterface;
use App\Models\Invitation;
use Illuminate\Support\Collection;

class InvitationRepository extends BaseRepository implements InvitationRepositoryInterface
{
    public function __construct(Invitation $model)
    {
        parent::__construct($model);
    }

    public function createInvitation(array $data): Invitation
    {
        return $this->model->create($data);
    }

    public function findByToken(string $token): ?Invitation
    {
        return $this->model
            ->where('token', $token)
            ->first();
    }

    public function pendingForOrganizationAndEmail(int $organizationId, string $email): ?Invitation
    {
        return $this->model
            ->where('organization_id', $organizationId)
            ->where('email', $email)
            ->where('status', InvitationStatus::Pending->value)
            ->where(function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    public function allForOrganization(int $organizationId): Collection
    {
        return $this->model
            ->where('organization_id', $organizationId)
            ->latest()
            ->get();
    }
}
