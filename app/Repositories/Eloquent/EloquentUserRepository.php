<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly User $model) {}

    public function getAllPaginated(int $perPage): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }
}
