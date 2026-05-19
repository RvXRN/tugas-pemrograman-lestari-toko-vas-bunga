<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Laravel\Octane\Facades\Octane;

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
        Model::preventLazyLoading(! app()->isProduction());

        // ─── Octane Stateless Guard ──────────────────────────────────────────
        // KRITIS: Pastikan tidak ada state yang bocor antar request di
        // FrankenPHP worker mode. Worker me-reuse PHP runtime, artinya
        // static properties atau singleton yang menyimpan state request
        // BISA terbawa ke request user berikutnya jika tidak di-flush.
        //
        // Guard ini mendaftarkan callback via Octane::tick() yang berjalan
        // setiap request selesai untuk reset state berbahaya.
        if (class_exists(Octane::class) && app()->bound('octane')) {
            Octane::tick('stateless-check', function () {
                // Placeholder: tambahkan reset state custom di sini jika ada
                // Contoh: MyStaticService::reset();
                // Contoh: SomeRepository::clearLocalCache();
            })->immediate();
        }
        // ─────────────────────────────────────────────────────────────────────

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url', 'http://localhost:3000') . '/reset-password?token=' . $token . '&email=' . $notifiable->getEmailForPasswordReset();
        });

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Anti-brute-force: Login maks 5 hit/menit per IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Mencegah spam registrasi massal
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Mencegah email bombing via forgot-password
        RateLimiter::for('forgot-password', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // Global API fallback rate limiter
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });
    }
}
