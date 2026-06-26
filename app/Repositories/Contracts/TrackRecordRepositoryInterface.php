<?php

namespace App\Repositories\Contracts;

use App\Models\TrackRecord;
use App\Models\TrackRecordItem;
use Illuminate\Database\Eloquent\Collection;

interface TrackRecordRepositoryInterface
{
    public function getAll(?int $year): Collection;

    public function findWithItems(int $id): ?TrackRecord;

    public function create(array $data): TrackRecord;

    public function createItem(TrackRecord $record, array $data): TrackRecordItem;

    public function update(TrackRecord $record, array $data): void;

    public function deleteItems(TrackRecord $record): void;

    public function delete(TrackRecord $record): void;
}
