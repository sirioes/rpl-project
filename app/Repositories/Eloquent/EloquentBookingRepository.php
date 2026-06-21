<?php

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;

class EloquentBookingRepository implements BookingRepositoryInterface
{
    public function __construct(private readonly Booking $model) {}

    public function getReservedQuantity(int $productId): int
    {
        return $this->model
            ->where('product_id', $productId)
            ->where('status', 'unpaid')
            ->where('created_at', '>=', now()->subHours(2))
            ->sum('quantity');
    }

    public function create(array $data): Booking
    {
        return $this->model->create($data);
    }

    public function findByStripeSessionId(string $sessionId, bool $withRelations = false): ?Booking
    {
        $query = $this->model->where('stripe_session_id', $sessionId);

        if ($withRelations) {
            $query->with(['product', 'participants']);
        }

        return $query->first();
    }

    public function updateStripeSessionId(Booking $booking, string $sessionId): void
    {
        $booking->update(['stripe_session_id' => $sessionId]);
    }

    public function markAsPaid(Booking $booking): void
    {
        $booking->update(['status' => 'paid']);
        $booking->product->decrement('ticket_quota', $booking->quantity);
    }
}
