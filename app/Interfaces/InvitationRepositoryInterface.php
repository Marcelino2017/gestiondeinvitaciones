<?php

namespace App\Interfaces;

use App\Models\Invitation;
use Illuminate\Support\Collection;

interface InvitationRepositoryInterface extends BaseRepositoryInterface
{
    public function createInvitation(array $data): Invitation;

    public function findByToken(string $token): ?Invitation;

    public function pendingForOrganizationAndEmail(int $organizationId, string $email): ?Invitation;

    public function allForOrganization(int $organizationId): Collection;
}
