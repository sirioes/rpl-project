<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Product;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(private readonly BookingRepositoryInterface $bookingRepository) {}

    public function createBooking(array $bookingData, array $participants): Booking
    {
        return DB::transaction(function () use ($bookingData, $participants) {
            $product = Product::lockForUpdate()->findOrFail($bookingData['product_id']);

            $reserved  = $this->bookingRepository->getReservedQuantity($product->id);
            $available = $product->ticket_quota - $reserved;

            if ($bookingData['quantity'] > $available) {
                throw new \Exception("Maaf, sisa tiket tersedia hanya {$available}. Silakan kurangi jumlah tiket.");
            }

            $booking = $this->bookingRepository->create($bookingData);

            foreach ($participants as $participant) {
                $booking->participants()->create([
                    'name'     => $participant['name'],
                    'category' => $participant['category'],
                ]);
            }

            return $booking;
        });
    }

    public function confirmPayment(string $sessionId): ?Booking
    {
        $booking = $this->bookingRepository->findByStripeSessionId($sessionId, withRelations: true);

        if (! $booking || $booking->status === 'paid') {
            return $booking;
        }

        $this->bookingRepository->markAsPaid($booking);

        return $booking;
    }

    public function updateStripeSession(Booking $booking, string $sessionId): void
    {
        $this->bookingRepository->updateStripeSessionId($booking, $sessionId);
    }
}
