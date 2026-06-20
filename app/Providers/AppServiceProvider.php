<?php

namespace App\Providers;

use App\Events\BookingPaid;
use App\Listeners\SendBookingConfirmationMail;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Blade::component('layouts.app', 'main');

        // Observer Pattern: setiap BookingPaid di-fire, listener ini otomatis dipanggil
        Event::listen(BookingPaid::class, SendBookingConfirmationMail::class);
    }
}
