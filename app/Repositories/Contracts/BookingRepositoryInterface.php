<?php

namespace App\Repositories\Contracts;

use App\Models\Booking;

interface BookingRepositoryInterface
{
    public function getReservedQuantity(int $productId): int;

    public function create(array $data): Booking;

    public function findByStripeSessionId(string $sessionId, bool $withRelations = false): ?Booking;

    public function updateStripeSessionId(Booking $booking, string $sessionId): void;

    public function markAsPaid(Booking $booking): void;
}
