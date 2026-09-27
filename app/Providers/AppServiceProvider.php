<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- Tambahkan baris ini

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
        // Saat diakses lewat tunnel publik (mis. *.loca.lt), TLS di-terminate
        // di server tunnel sehingga Laravel mengira request-nya HTTP biasa dan
        // membuat URL form/redirect ber-scheme http://. Browser lalu memblokir
        // submit form login sebagai "not secure" (berujung 419 di HP).
        // Paksa scheme https agar semua URL yang dibuat Laravel aman.
        try {
            $host = request()->getHost();
            if (str_ends_with($host, '.loca.lt') || request()->header('X-Forwarded-Proto') === 'https') {
                URL::forceScheme('https');
                // Beri tahu Request bahwa koneksi aslinya HTTPS, supaya
                // $request->fullUrl() / session previous-url / redirect()->back()
                // juga memakai https meski tanpa header Referer.
                request()->server->set('HTTPS', 'on');
            }
        } catch (\Throwable $e) {
            // Abaikan saat tidak ada request aktif (mis. artisan CLI).
        }
    }
}