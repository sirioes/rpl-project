<?php

namespace App\Repositories\Contracts;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

interface MessageRepositoryInterface
{
    public function getAll(): Collection;

    public function getUnread(): Collection;

    public function findById(int $id): ?Message;

    public function create(array $data): Message;

    public function markAsRead(Message $message): void;

    public function delete(Message $message): void;
}
