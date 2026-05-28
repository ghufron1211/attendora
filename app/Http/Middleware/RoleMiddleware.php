<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Jika role tidak sesuai:
     * - Request AJAX/JSON → 403 Forbidden
     * - Request biasa → redirect ke dashboard
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user() || $request->user()->role !== $role) {
            if ($request->expectsJson() || $request->ajax()) {
                abort(403, 'Akses ditolak. Halaman ini hanya untuk role ' . $role . '.');
            }

            return redirect('/dashboard')->with('error', 'Akses ditolak. Halaman tersebut tidak tersedia untuk role Anda.');
        }

        return $next($request);
    }
}
