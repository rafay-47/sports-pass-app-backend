<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTrainerRole
{
    /**
     * Handle an incoming request.
     * Allows access if user is a trainer OR has admin/owner role
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
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

        // Allow access if user is:
        // 1. A trainer (is_trainer = true)
        // 2. Has admin or owner role
        if (!$user->is_trainer && !in_array($user->user_role, ['admin', 'owner'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Trainer privileges or admin/owner role required.',
                'user_role' => $user->user_role,
                'is_trainer' => $user->is_trainer
            ], 403);
        }

        return $next($request);
    }
}
