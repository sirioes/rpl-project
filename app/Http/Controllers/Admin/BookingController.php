<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private readonly BookingRepositoryInterface $bookingRepository) {}

    public function index()
    {
        $bookings     = $this->bookingRepository->getAllWithRelations();
        $totalRevenue = $this->bookingRepository->getTotalRevenue();
        $totalPaid    = $this->bookingRepository->getTotalPaid();
        $totalUnpaid  = $this->bookingRepository->getTotalUnpaid();

        return view('admin.bookings.index', compact('bookings', 'totalRevenue', 'totalPaid', 'totalUnpaid'));
    }

    public function update(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:paid,unpaid',
        ]);

        $this->bookingRepository->updateStatus($booking, $request->status);

        return back()->with('success', "Booking {$booking->booking_reference} status updated to {$request->status}.");
    }

    public function destroy(Booking $booking)
    {
        $this->bookingRepository->delete($booking);

        return back()->with('success', "Booking {$booking->booking_reference} has been deleted.");
    }
}
