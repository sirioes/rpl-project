<?php

namespace App\Providers;

use App\Events\BookingPaid;
use App\Listeners\SendBookingConfirmationMail;
use App\Services\DeepLService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeepLService::class, fn() => new DeepLService());
    }

    public function boot(): void
    {
        Blade::component('layouts.app', 'main');
        Event::listen(BookingPaid::class, SendBookingConfirmationMail::class);
    }
}
