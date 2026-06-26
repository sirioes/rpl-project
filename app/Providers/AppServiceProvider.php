<?php

namespace App\Providers;

use App\Events\BookingPaid;
use App\Listeners\SendBookingConfirmationMail;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\TrackRecordRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentBookingRepository;
use App\Repositories\Eloquent\EloquentProductRepository;
use App\Repositories\Eloquent\EloquentTrackRecordRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\DeepLService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(BookingRepositoryInterface::class, EloquentBookingRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(TrackRecordRepositoryInterface::class, EloquentTrackRecordRepository::class);
        $this->app->singleton(DeepLService::class, fn() => new DeepLService());
    }

    public function boot(): void
    {
        Blade::component('layouts.app', 'main');
        Event::listen(BookingPaid::class, SendBookingConfirmationMail::class);
    }
}
