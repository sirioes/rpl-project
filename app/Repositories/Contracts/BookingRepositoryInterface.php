<?php

namespace App\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

interface BookingRepositoryInterface
{
    public function getReservedQuantity(int $productId): int;

    public function create(array $data): Booking;

    public function findByStripeSessionId(string $sessionId, bool $withRelations = false): ?Booking;

    public function updateStripeSessionId(Booking $booking, string $sessionId): void;

    public function markAsPaid(Booking $booking): void;

    public function getAllWithRelations(): Collection;

    public function getTotalRevenue(): float;

    public function getTotalPaid(): int;

    public function getTotalUnpaid(): int;

    public function updateStatus(Booking $booking, string $newStatus): void;

    public function delete(Booking $booking): void;
}
