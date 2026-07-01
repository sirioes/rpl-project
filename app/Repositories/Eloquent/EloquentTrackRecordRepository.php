<?php

namespace App\Repositories\Eloquent;

use App\Models\TrackRecord;
use App\Models\TrackRecordItem;
use App\Repositories\Contracts\TrackRecordRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentTrackRecordRepository implements TrackRecordRepositoryInterface
{
    public function __construct(private readonly TrackRecord $model) {}

    public function getAll(?int $year): Collection
    {
        return $this->model->when($year, fn ($q) => $q->where('year', $year))
            ->latest()
            ->get();
    }

    public function findWithItems(int $id): ?TrackRecord
    {
        return $this->model->with('items')->find($id);
    }

    public function create(array $data): TrackRecord
    {
        return $this->model->create($data);
    }

    public function createItem(TrackRecord $record, array $data): TrackRecordItem
    {
        return $record->items()->create($data);
    }

    public function update(TrackRecord $record, array $data): void
    {
        $record->update($data);
    }

    public function deleteItems(TrackRecord $record): void
    {
        $record->items()->delete();
    }

    public function delete(TrackRecord $record): void
    {
        $record->delete();
    }
}
