<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user = $request->user();

        // Check if user is active
        if (!$user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account is deactivated'
            ], 403);
        }

        // Check if user has required role
        if (!in_array($user->user_role, $roles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient permissions. Required roles: ' . implode(', ', $roles),
                'user_role' => $user->user_role
            ], 403);
        }

        return $next($request);
    }
}
