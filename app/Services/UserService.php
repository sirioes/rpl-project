<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function getPaginatedUsers(int $perPage = 10): LengthAwarePaginator
    {
        return $this->userRepository->getAllPaginated($perPage);
    }

    public function findUser(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }
}
