<?php

namespace App\Providers;

use App\Events\PembayaranDibuat;
use App\Listeners\HantarNotifikasiPembayaran;
use App\Listeners\LogPembayaranBaru;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(PembayaranDibuat::class, LogPembayaranBaru::class);
        Event::listen(PembayaranDibuat::class, HantarNotifikasiPembayaran::class);
    }
}
