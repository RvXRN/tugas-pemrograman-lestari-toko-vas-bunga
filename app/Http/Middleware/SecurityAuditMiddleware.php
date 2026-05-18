<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Audit Logging Middleware (Celah #10: Insufficient Logging & Monitoring)
 *
 * Mencatat semua kejadian keamanan penting agar admin dapat mendeteksi
 * pola serangan sedini mungkin melalui review log berkala atau SIEM.
 */
class SecurityAuditMiddleware
{
    /** Response codes yang dianggap sebagai "security event" */
    protected array $suspiciousStatusCodes = [401, 403, 404, 405, 422, 429];

    /** Endpoint yang selalu dilog karena sensitif */
    protected array $sensitiveEndpoints = [
        'auth/login',
        'auth/register',
        'auth/forgot-password',
        'auth/reset-password',
        'checkout',
        'payments/webhook',
        'admin',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $statusCode = $response->getStatusCode();
        $path = $request->path();
        $isSensitive = $this->isSensitiveEndpoint($path);

        // Log semua request ke endpoint sensitif dan semua respons mencurigakan
        if ($isSensitive || in_array($statusCode, $this->suspiciousStatusCodes)) {
            $level = $this->resolveLogLevel($statusCode);

            Log::$level('[SECURITY AUDIT]', [
                'method' => $request->method(),
                'path' => $path,
                'status' => $statusCode,
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id ?? 'guest',
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }

    protected function isSensitiveEndpoint(string $path): bool
    {
        foreach ($this->sensitiveEndpoints as $endpoint) {
            if (str_contains($path, $endpoint)) {
                return true;
            }
        }
        return false;
    }

    protected function resolveLogLevel(int $statusCode): string
    {
        return match (true) {
            $statusCode === 429 => 'warning',   // Rate limit hit — possible brute force
            $statusCode === 403 => 'warning',   // Forbidden — possible privilege escalation
            $statusCode === 401 => 'info',      // Unauthenticated — normal user flow
            $statusCode >= 500 => 'error',      // Server error
            default => 'info',
        };
    }
}
