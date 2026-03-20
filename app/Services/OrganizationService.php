<?php

namespace App\Services;

use App\Http\Resources\OrganizationResource;
use App\Interfaces\OrganizationRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use App\Exceptions\CustomException;

class OrganizationService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private UserRepositoryInterface $userRepository,
    ) {}

    public function all(array $args = [])
    {
        $page = $args['page'] ?? 1;
        $perPage = $args['perPage'] ?? 10;
        $search = $args['search'] ?? null;
        $state = $args['state'] ?? 'active';
        $stateColumn = $args['stateColumn'] ?? 'state';
        $columns = $args['columns'] ?? ['*'];
        $orderBy = $args['orderBy'] ?? '';

        $organizations = $this->organizationRepository->all(
            $page,
            $perPage,
            $search,
            $columns,
            $orderBy,
            $stateColumn,
            $state,
        );

        return OrganizationResource::collection($organizations);
    }

    public function listForUser(int $userId): AnonymousResourceCollection
    {
        $organizations = $this->organizationRepository->all();

        return OrganizationResource::collection($organizations);
    }



    public function createForUser(array $data, int $userId): OrganizationResource
    {
        DB::beginTransaction();
        try {
            $organization = $this->organizationRepository->create($data);
            $organization->users()->attach($userId, ['role' => 'admin']);
            DB::commit();
            return new OrganizationResource($organization->fresh('users'));
        } catch (\Throwable $e) {
            DB::rollBack();
            throw new CustomException('Error creating organization: ' . $e->getMessage(), 500);
        }
    }

    public function show(int $organization): OrganizationResource
    {
        try {
            $organization = $this->organizationRepository->findOrFail($organization);
            return new OrganizationResource($organization);
        } catch (\Exception $e) {
            throw new CustomException('Error showing organization: ' . $e->getMessage(), 500);
        }
    }

    public function update(int $organization, array $data): OrganizationResource
    {
        $organization = $this->organizationRepository->findOrFail($organization);
        $organization->update($data);
        return new OrganizationResource($organization->fresh('users'));
    }

    public function destroy(int $organizationId): void
    {
        $organization = $this->organizationRepository->findOrFail($organizationId);
        $organization->delete();
    }

    public function listUsers(int $organizationId, int $actorUserId): array
    {
        $organization = $this->organizationRepository->findOrFail($organizationId);
        $this->ensureAdminMembership($organization, $actorUserId);

        return $organization->users()
            ->select('users.id', 'users.name', 'users.email')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->pivot->role,
                'joined_at' => $user->pivot->created_at,
            ])
            ->values()
            ->all();
    }

    public function updateUserRole(int $organizationId, int $targetUserId, string $role, int $actorUserId): array
    {
        $organization = $this->organizationRepository->findOrFail($organizationId);
        $this->ensureAdminMembership($organization, $actorUserId);

        $targetMembership = $organization->users()->where('users.id', $targetUserId)->first();

        if (! $targetMembership) {
            throw new CustomException('Usuario no pertenece a la organizacion.', 404);
        }

        $organization->users()->updateExistingPivot($targetUserId, [
            'role' => $role,
            'updated_at' => now(),
        ]);

        $updatedMembership = $organization->users()->where('users.id', $targetUserId)->firstOrFail();

        return [
            'id' => $updatedMembership->id,
            'name' => $updatedMembership->name,
            'email' => $updatedMembership->email,
            'role' => $updatedMembership->pivot->role,
        ];
    }

    public function removeUser(int $organizationId, int $targetUserId, int $actorUserId): void
    {
        $organization = $this->organizationRepository->findOrFail($organizationId);
        $this->ensureAdminMembership($organization, $actorUserId);

        $targetMembership = $organization->users()->where('users.id', $targetUserId)->first();

        if (! $targetMembership) {
            throw new CustomException('Usuario no pertenece a la organizacion.', 404);
        }

        $adminCount = $organization->users()->wherePivot('role', 'admin')->count();

        if ($targetMembership->pivot->role === 'admin' && $adminCount === 1) {
            throw new CustomException('No se puede eliminar al unico admin de la organizacion.', 409);
        }

        $organization->users()->detach($targetUserId);
    }

    public function createUser(int $organizationId, array $data, int $actorUserId): array
    {
        $organization = $this->organizationRepository->findOrFail($organizationId);
        $this->ensureAdminMembership($organization, $actorUserId);

        $existingUser = $this->userRepository->findByEmail($data['email']);

        if ($existingUser) {
            $alreadyMember = $organization->users()->where('users.id', $existingUser->id)->exists();

            if ($alreadyMember) {
                throw new CustomException('El usuario ya pertenece a la organizacion.', 409);
            }

            $organization->users()->attach($existingUser->id, [
                'role' => $data['role'],
            ]);

            return [
                'id' => $existingUser->id,
                'name' => $existingUser->name,
                'email' => $existingUser->email,
                'role' => $data['role'],
            ];
        }

        $newUser = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $organization->users()->attach($newUser->id, [
            'role' => $data['role'],
        ]);

        return [
            'id' => $newUser->id,
            'name' => $newUser->name,
            'email' => $newUser->email,
            'role' => $data['role'],
        ];
    }

    private function ensureAdminMembership($organization, int $actorUserId): void
    {
        $membership = $organization->users()
            ->where('users.id', $actorUserId)
            ->first();

        if (! $membership || $membership->pivot->role !== 'admin') {
            throw new CustomException('Solo un admin de la organizacion puede realizar esta accion.', 403);
        }
    }
}
