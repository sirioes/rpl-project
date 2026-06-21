<?php

namespace App\Listeners;

use App\Events\BookingPaid;
use App\Mail\BookingConfirmationMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmationMail
{
    public function handle(BookingPaid $event): void
    {
        try {
            Mail::to($event->booking->contact_email)
                ->send(new BookingConfirmationMail($event->booking));
        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation email: '.$e->getMessage());
        }
    }
}
