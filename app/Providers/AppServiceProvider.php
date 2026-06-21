<?php

namespace App\Providers;


use App\Events\BookingPaid;
use App\Listeners\SendBookingConfirmationMail;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Eloquent\EloquentBookingRepository;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(BookingRepositoryInterface::class, EloquentBookingRepository::class);
    }

    public function boot(): void
    {
        Blade::component('layouts.app', 'main');
        // Observer Pattern: setiap BookingPaid di-fire, listener ini otomatis dipanggil
        Event::listen(BookingPaid::class, SendBookingConfirmationMail::class);
    }
}
