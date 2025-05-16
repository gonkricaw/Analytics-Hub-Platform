<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        if (!auth()->user()->hasPermission($permission)) {
            return response()->json([
                'message' => 'You do not have permission to access this resource'
            ], 403);
        }

        return $next($request);
    }
}
