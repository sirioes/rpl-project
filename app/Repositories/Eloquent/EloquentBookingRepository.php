<?php

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

    public function getAllWithRelations(): Collection
    {
        return $this->model
            ->with(['user', 'product', 'participants'])
            ->orderByRaw("CASE WHEN status = 'paid' THEN 1 ELSE 2 END")
            ->latest()
            ->get();
    }

    public function getTotalRevenue(): float
    {
        return (float) $this->model->where('status', 'paid')->sum('total_price');
    }

    public function getTotalPaid(): int
    {
        return $this->model->where('status', 'paid')->count();
    }

    public function getTotalUnpaid(): int
    {
        return $this->model->where('status', 'unpaid')->count();
    }

    public function updateStatus(Booking $booking, string $newStatus): void
    {
        DB::transaction(function () use ($booking, $newStatus) {
            $oldStatus = $booking->status;

            if ($oldStatus === 'unpaid' && $newStatus === 'paid') {
                $booking->product->decrement('ticket_quota', $booking->quantity);
            }

            if ($oldStatus === 'paid' && $newStatus === 'unpaid') {
                $booking->product->increment('ticket_quota', $booking->quantity);
            }

            $booking->update(['status' => $newStatus]);
        });
    }

    public function delete(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            if ($booking->status === 'paid') {
                $booking->product->increment('ticket_quota', $booking->quantity);
            }

            $booking->delete();
        });
    }
}
