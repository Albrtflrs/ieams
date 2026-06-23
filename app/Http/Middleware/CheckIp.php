<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;

class CheckIp
{
    public function handle(Request $request, Closure $next)
    {
        $allowedIps = Setting::get('allowed_ips', '127.0.0.1');
        $ips = array_map('trim', explode(',', $allowedIps));

        $clientIp = $request->ip();

        foreach ($ips as $ip) {
            if (fnmatch($ip, $clientIp)) {
                return $next($request);
            }
        }

        abort(403, 'IP address not allowed.');
    }
}