<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(
        Request $request,
        Closure $next
    ): Response|JsonResponse|RedirectResponse {
        if (! $request->user() || $request->user()->activo) {
            return $next($request);
        }

        if ($request->is('api/*') || $request->expectsJson()) {
            Auth::guard('api')->logout();

            return response()->json([
                'message' => 'La cuenta se encuentra inactiva.',
            ], 403);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors([
                'email' => 'Tu cuenta se encuentra desactivada. Comunícate con el administrador.',
            ]);
    }
}
