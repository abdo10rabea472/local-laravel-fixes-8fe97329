<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Converts RedirectResponse → JSON when the request expects JSON (XHR).
 * Lets controllers keep returning redirect()->back()->with('success', ...)
 * while still serving AJAX clients with structured payloads.
 */
class AjaxResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->expectsJson() && ! $request->ajax()) {
            return $response;
        }

        if ($response instanceof RedirectResponse) {
            $session = $request->hasSession() ? $request->session() : null;
            $success = $session?->get('success');
            $error = $session?->get('error');

            $payload = [
                'success' => $error ? false : true,
                'message' => $success ?? $error ?? 'تم',
                'redirect' => $response->getTargetUrl(),
            ];

            // Consume flash so a later full page load doesn't re-show it
            $session?->forget(['success', 'error']);

            return response()->json($payload, $error ? 422 : 200);
        }

        return $response;
    }
}
