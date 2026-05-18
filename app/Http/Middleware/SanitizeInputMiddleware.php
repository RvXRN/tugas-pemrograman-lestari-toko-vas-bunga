<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInputMiddleware
{
    /**
     * Sanitize all incoming string inputs to prevent XSS injection.
     * This acts as a last-resort defense; primary defense is
     * proper output encoding in API Resources.
     *
     * NOTE: This strips HTML tags from all string inputs.
     * If you have fields that legitimately accept HTML (e.g. rich text),
     * add them to the $except list.
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        $sanitized = $this->sanitize($input);
        $request->merge($sanitized);

        return $next($request);
    }

    protected function sanitize(array $input): array
    {
        foreach ($input as $key => $value) {
            if (in_array($key, $this->except)) {
                continue;
            }

            if (is_string($value)) {
                // Strip all HTML/PHP tags and encode special characters
                $input[$key] = htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            } elseif (is_array($value)) {
                $input[$key] = $this->sanitize($value);
            }
        }

        return $input;
    }
}
