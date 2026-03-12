<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireDebugToken
{
    /**
     * Require a DEBUG_TOKEN to access /debug/* endpoints.
     *
     * If no token is configured, treat debug endpoints as disabled (404) to
     * avoid accidental public exposure in production.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) config('runtime.debug_token', '');
        if ($token === '') {
            abort(404);
        }

        $provided = (string) $request->query('token', '');
        if (!hash_equals($token, $provided)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}

