<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     * Mencatat setiap kunjungan ke halaman dengan IP dan user agent.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Catat kunjungan
        Visitor::create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'page'       => $request->path(),
            'visited_at' => now(),
        ]);

        return $next($request);
    }
}
