<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsPemimpinPerusahaan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'Pemimpin Perusahaan') {
            abort(403, 'Akses ditolak. Hanya Pemimpin Perusahaan yang dapat mengakses halaman ini.');
        }
        return $next($request);
    }
}
