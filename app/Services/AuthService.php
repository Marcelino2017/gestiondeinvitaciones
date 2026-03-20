<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Http\Resources\UserResource;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
    )
    {}


    public function register(array $data): array
    {
        try {
            $user = $this->userRepository->create($data);
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ];
        } catch (\Exception $e) {
            throw new CustomException('Error registering user: ' . $e->getMessage(), 500);
        }
    }


    public function login(array $data): array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw new CustomException('Credenciales incorrectas.', 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function logout(int $userId): void
    {
        $user = $this->userRepository->findOrFail($userId);
        $user->currentAccessToken()?->delete();
    }
}
