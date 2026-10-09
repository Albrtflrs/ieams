<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;

class CheckIp
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the settings table actually exists before querying it
        try {
            // Cache the IP list for 60 minutes to reduce database load
            $allowedIps = Cache::remember('allowed_ips_list', 3600, function () {
                return Setting::get('allowed_ips', '127.0.0.1');
            });
        } catch (QueryException $e) {
            // If the table doesn't exist, fail safely to localhost only
            // (or set to a default IP range that works for your LAN)
            $allowedIps = '127.0.0.1'; 
        }

        $ips = array_map('trim', explode(',', $allowedIps));
        $clientIp = $request->ip();

        // Allow localhost and the matched IPs
        foreach ($ips as $ip) {
            // fnmatch handles wildcards like 192.168.1.* perfectly on Windows
            if (fnmatch($ip, $clientIp)) {
                return $next($request);
            }
        }

        abort(403, 'Your IP address (' . $clientIp . ') is not authorized to access this system.');
    }
}