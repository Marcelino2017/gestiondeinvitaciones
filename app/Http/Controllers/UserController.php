<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::query()
            ->with(['roles', 'organizations'])
            ->latest()
            ->get();

        return $this->successResponse(
            UserResource::collection($users),
            'Usuarios obtenidos correctamente.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->assignRole($data['role'] ?? 'member');

        if (! empty($data['organization_ids'])) {
            $user->organizations()->syncWithPivotValues(
                $data['organization_ids'],
                ['role' => $data['organization_role'] ?? 'member']
            );
        }

        $user->load('roles', 'organizations');

        return $this->successResponse(new UserResource($user), 'Usuario creado correctamente.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
