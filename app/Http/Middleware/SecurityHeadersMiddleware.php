<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * HTTP Security Headers — Prevents: Clickjacking, MIME Sniffing, XSS,
     * Information Disclosure (stack fingerprinting), and enforces HTTPS.
     *
     * Also strips dangerous incoming headers used for proxy-based
     * access control bypass attacks (e.g. X-Original-URL override).
     */

    /**
     * These incoming request headers can be abused by attackers to trick
     * reverse proxies into routing requests to unintended endpoints,
     * effectively bypassing access control (URL override attack).
     *
     * References:
     *  - CVE: Squid/IIS X-Original-URL bypass
     *  - OWASP: Proxy Header Injection
     */
    protected array $dangerousRequestHeaders = [
        'X-Original-URL',       // IIS / Squid — URL override
        'X-Rewrite-URL',        // IIS URL Rewrite module
        'X-Forwarded-Host',     // Can poison Host header used in password reset links
        'X-Host',               // Variant of X-Forwarded-Host
        'X-Forwarded-Server',
        'X-HTTP-Method-Override', // Allows POST to masquerade as DELETE/PUT
        'X-HTTP-Method',
        'X-Method-Override',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // === STRIP DANGEROUS INCOMING HEADERS ===
        // Must be done BEFORE passing to the next middleware/controller.
        foreach ($this->dangerousRequestHeaders as $header) {
            $request->headers->remove($header);
        }

        $response = $next($request);

        // Prevent MIME-type sniffing (e.g., serving JS as image to bypass CSP)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent Clickjacking attacks (embedding this API in iframes)
        $response->headers->set('X-Frame-Options', 'DENY');

        // Enforce HTTPS for 1 year (only in production)
        if (app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Do not leak referrer URL to third-parties
        $response->headers->set('Referrer-Policy', 'no-referrer');

        // Disable browser features that could be abused
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');

        // Modern standard: disable legacy XSS auditor (can cause issues), let CSP handle it
        $response->headers->set('X-XSS-Protection', '0');

        // Prevent caching of sensitive API responses
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        // Hide stack fingerprint — remove headers that reveal technology stack
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
