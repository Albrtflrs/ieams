<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Setting;

class CheckRegistration
{
    public function handle($request, Closure $next)
    {
        if (!Setting::get('enable_registration', false)) {
            abort(404, 'Registration is disabled.');
        }
        return $next($request);
    }
}