<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApprovalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            abort(401, 'Unauthorized');
        }

        $user = auth()->user();

        // Define leadership roles that can approve criteria and weight assessments
        $leadershipRoles = ['Pemimpin Perusahaan'];

        // Check if user has leadership role for approval functions
        if (!in_array($user->role, $leadershipRoles)) {
            abort(403, 'Akses ditolak. Hanya Pemimpin Perusahaan yang dapat mengakses fungsi persetujuan.');
        }

        return $next($request);
    }
}
