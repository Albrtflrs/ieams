<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // 🔥 Super-admin bypass – always allowed, no matter what
        if (trim(strtolower(auth()->user()->role)) === 'super_admin') {
            return $next($request);
        }

        // If no roles are specified for this route, deny access
        if (empty($roles)) {
            abort(403, 'No roles specified for this route.');
        }

        if (!in_array(auth()->user()->role, $roles)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}