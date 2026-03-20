<?php

namespace App\Http\Controllers;

use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Requests\Organization\StoreOrganizationUserRequest;
use App\Http\Requests\Organization\UpdateOrganizationRequest;
use App\Http\Requests\Organization\UpdateOrganizationUserRoleRequest;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct(
        private OrganizationService $organizationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $organizations = $this->organizationService->all($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Organizaciones obtenidas correctamente.',
            'data' => $organizations,
        ], 200);
    }

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        $organization = $this->organizationService
            ->createForUser($request->validated(), $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Organizacion creada correctamente.',
            'data' => $organization,
        ], 201);
    }

    public function show(int $organization): JsonResponse
    {
        $organization = $this->organizationService->show($organization);

        return response()->json([
            'success' => true,
            'message' => 'Organizacion obtenida correctamente.',
            'data' => $organization,
        ], 200);
    }

    public function update(UpdateOrganizationRequest $request, int $organization): JsonResponse
    {
        $organization = $this->organizationService->update($organization, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Organizacion actualizada correctamente.',
            'data' => $organization,
        ], 200);
    }

    public function destroy(int $organization): JsonResponse
    {
        $this->organizationService->destroy($organization);

        return response()->json([
            'success' => true,
            'message' => 'Organizacion eliminada correctamente.',
            'data' => null,
        ], 200);
    }

    public function users(Request $request, int $organization): JsonResponse
    {
        $users = $this->organizationService->listUsers($organization, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Usuarios obtenidos correctamente.',
            'data' => $users,
        ], 200);
    }

    public function updateUserRole(
        UpdateOrganizationUserRoleRequest $request,
        int $organization,
        int $user
    ): JsonResponse {
        $updatedUser = $this->organizationService->updateUserRole(
            $organization,
            $user,
            $request->validated()['role'],
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Rol del usuario actualizado correctamente.',
            'data' => $updatedUser,
        ], 200);
    }

    public function removeUser(Request $request, int $organization, int $user): JsonResponse
    {
        $this->organizationService->removeUser($organization, $user, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Usuario removido de la organizacion correctamente.',
            'data' => null,
        ], 200);
    }

    public function createUser(
        StoreOrganizationUserRequest $request,
        int $organization
    ): JsonResponse {
        $user = $this->organizationService->createUser(
            $organization,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado y agregado a la organizacion correctamente.',
            'data' => $user,
        ], 201);
    }
}
