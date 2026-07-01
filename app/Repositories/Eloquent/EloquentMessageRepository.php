<?php

namespace App\Repositories\Eloquent;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentMessageRepository implements MessageRepositoryInterface
{
    public function __construct(private readonly Message $model) {}

    public function getAll(): Collection
    {
        return $this->model->latest()->get();
    }

    public function getUnread(): Collection
    {
        return $this->model->where('is_read', false)->latest()->get();
    }

    public function findById(int $id): ?Message
    {
        return $this->model->find($id);
    }

    public function create(array $data): Message
    {
        return $this->model->create($data);
    }

    public function markAsRead(Message $message): void
    {
        if (! $message->is_read) {
            $message->update(['is_read' => true]);
        }
    }

    public function delete(Message $message): void
    {
        $message->delete();
    }
}
