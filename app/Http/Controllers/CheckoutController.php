<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use App\Models\Product;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService) {}

    public function process(Request $request, Product $product)
    {
        if ($product->isExpired()) {
            return back()->withErrors(['error' => 'Produk ini sudah expired dan tidak bisa dipesan.']);
        }

        $request->validate([
            'quantity'                   => 'required|integer|min:1',
            'contact_email'              => 'required|email',
            'contact_phone'              => 'required|string|max:20',
            'participants'               => 'required|array',
            'participants.*.name'        => 'required|string|max:255',
            'participants.*.category'    => 'required|in:Adult,Child',
        ]);

        try {
            $booking = $this->checkoutService->createBooking(
                bookingData: [
                    'booking_reference' => 'BKG-'.date('Ymd').'-'.strtoupper(uniqid()),
                    'user_id'           => auth()->id(),
                    'product_id'        => $product->id,
                    'quantity'          => $request->quantity,
                    'total_price'       => $product->product_price * $request->quantity,
                    'status'            => 'unpaid',
                    'contact_email'     => $request->contact_email,
                    'contact_phone'     => $request->contact_phone,
                ],
                participants: $request->participants,
            );

            Stripe::setApiKey(config('services.stripe.secret'));

            $stripeSession = Session::create([
                'payment_method_types' => ['card', 'ideal'],
                'line_items'           => [[
                    'price_data' => [
                        'currency'     => 'eur',
                        'product_data' => [
                            'name'        => $product->product_name,
                            'description' => 'Tanggal Keberangkatan: '.\Carbon\Carbon::parse($product->departure_date)->format('d M Y, H:i'),
                        ],
                        'unit_amount' => intval($product->product_price * 100),
                    ],
                    'quantity' => $request->quantity,
                ]],
                'mode'        => 'payment',
                'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('checkout.cancel'),
            ]);

            $this->checkoutService->updateStripeSession($booking, $stripeSession->id);

            return redirect($stripeSession->url);

        } catch (\Exception $e) {
            Log::error('Checkout Error: '.$e->getMessage());

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function details(Request $request, Product $product)
    {
        if ($product->isExpired()) {
            return redirect()->back()->withErrors(['error' => 'Produk ini sudah expired dan tidak bisa dipesan.']);
        }

        $quantity = $request->query('quantity', 1);

        if ($quantity < 1 || $quantity > $product->ticket_quota) {
            return redirect()->back()->withErrors(['error' => 'Jumlah tiket tidak valid.']);
        }

        return view('profile.checkout-details', compact('product', 'quantity'));
    }

    public function success(Request $request)
    {
        $booking = $this->checkoutService->confirmPayment($request->get('session_id'));

        if (! $booking) {
            abort(404);
        }

        if ($booking->wasChanged('status')) {
            try {
                Mail::to($booking->contact_email)->send(new BookingConfirmationMail($booking));
            } catch (\Exception $e) {
                Log::error('Failed to send booking confirmation email: '.$e->getMessage());
            }
        }

        return redirect()->route('profile.booking')->with('success', 'Payment successful! Here is your E-Ticket.');
    }

    public function repay(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->status !== 'unpaid') {
            return back()->withErrors(['error' => 'Only unpaid bookings can be retried.']);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $stripeSession = Session::create([
                'payment_method_types' => ['card', 'ideal'],
                'line_items'           => [[
                    'price_data' => [
                        'currency'     => 'eur',
                        'product_data' => [
                            'name'        => $booking->product->product_name,
                            'description' => 'Tanggal Keberangkatan: '.\Carbon\Carbon::parse($booking->product->departure_date)->format('d M Y, H:i'),
                        ],
                        'unit_amount' => intval($booking->product->product_price * 100),
                    ],
                    'quantity' => $booking->quantity,
                ]],
                'mode'        => 'payment',
                'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('checkout.cancel'),
            ]);

            $this->checkoutService->updateStripeSession($booking, $stripeSession->id);

            return redirect($stripeSession->url);

        } catch (\Exception $e) {
            Log::error('Repay Error: '.$e->getMessage());

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel()
    {
        return redirect('/products')->with('error', 'Payment Cancelled');
    }
}
