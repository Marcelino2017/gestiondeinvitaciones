<?php

namespace App\Services;

use App\Interfaces\UserRepositoryInterface;

class AuthService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
    )
    {}

    public function register(array $data)
    {
        $user = $this->userRepository->create($data);
        return $user;
    }


    public function login(array $data)
    {
        //
    }
}
