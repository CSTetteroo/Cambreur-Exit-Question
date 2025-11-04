<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsDocent
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'docent') {
            return $next($request);
        }
        abort(403, 'Alleen docenten mogen deze pagina bekijken.');
    }
}
