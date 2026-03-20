<?php

namespace App\Services;

use App\Enums\InvitationStatus;
use App\Http\Resources\InvitationResource;
use App\Interfaces\InvitationRepositoryInterface;
use App\Interfaces\OrganizationRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Jobs\SendOrganizationInvitationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InvitationService
{
    public function __construct(
        private InvitationRepositoryInterface $invitationRepository,
        private OrganizationRepositoryInterface $organizationRepository,
        private UserRepositoryInterface $userRepository,
    ) {}

    public function createForOrganization(array $data, int $organizationId, int $inviterId): InvitationResource
    {
        $organization = $this->organizationRepository->findOrFail($organizationId);

        $existingInvitation = $this->invitationRepository
            ->pendingForOrganizationAndEmail($organizationId, $data['email']);

        if ($existingInvitation) {
            throw new HttpException(409, 'Ya existe una invitacion pendiente para este email en la organizacion.');
        }

        $invitation = $this->invitationRepository->createInvitation([
            'user_id' => $inviterId,
            'organization_id' => $organizationId,
            'email' => $data['email'],
            'role' => $data['role'],
            'token' => Str::uuid()->toString(),
            'status' => InvitationStatus::Pending->value,
            'expires_at' => now()->addDays(7),
        ]);

        SendOrganizationInvitationJob::dispatch($organization->id, $invitation->id)
            ->onQueue('emails');

        return new InvitationResource($invitation->fresh('organization', 'user'));
    }

    public function showByToken(string $token): InvitationResource
    {
        $invitation = $this->invitationRepository->findByToken($token);

        if (! $invitation) {
            throw new HttpException(404, 'Invitacion no encontrada.');
        }

        $this->expireIfNeeded($invitation);

        return new InvitationResource($invitation->fresh('organization', 'user'));
    }

    public function acceptByToken(string $token, array $data): InvitationResource
    {
        $invitation = $this->invitationRepository->findByToken($token);

        if (! $invitation) {
            throw new HttpException(404, 'Invitacion no encontrada.');
        }

        $this->expireIfNeeded($invitation);

        if ($invitation->status !== InvitationStatus::Pending) {
            throw new HttpException(409, 'La invitacion ya fue procesada.');
        }

        if (! empty($data['user_id'])) {
            $user = $this->userRepository->findOrFail((int) $data['user_id']);

            if ($user->email !== $invitation->email) {
                throw new HttpException(409, 'El usuario autenticado no coincide con el email invitado.');
            }
        } else {
            $user = $this->userRepository->findByEmail($invitation->email);

            if (! $user) {
                $user = $this->userRepository->create([
                    'name' => $data['name'],
                    'email' => $invitation->email,
                    'password' => $data['password'],
                ]);
            }
        }

        DB::transaction(function () use ($invitation, $user) {
            $organization = $invitation->organization()->firstOrFail();

            $organization->users()->syncWithoutDetaching([
                $user->id => ['role' => $invitation->role],
            ]);

            $invitation->update([
                'status' => InvitationStatus::Accepted->value,
                'accepted_at' => now(),
            ]);
        });

        return new InvitationResource($invitation->fresh('organization', 'user'));
    }

    private function expireIfNeeded($invitation): void
    {
        if (! $invitation->expires_at || now()->lte($invitation->expires_at)) {
            return;
        }

        if ($invitation->status === InvitationStatus::Pending) {
            $invitation->update(['status' => InvitationStatus::Expired->value]);
        }

        throw new HttpException(409, 'La invitacion ha expirado.');
    }
}
