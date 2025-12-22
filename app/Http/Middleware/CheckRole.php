<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $user = $request->user();
        $userRole = strtoupper($user->role);

        // Normalize roles to uppercase for comparison
        $allowedRoles = array_map('strtoupper', $roles);

        // Check if user has one of the allowed roles
        if (in_array($userRole, $allowedRoles)) {
            return $next($request);
        }

        // User doesn't have permission - redirect to their appropriate dashboard
        return $this->redirectToRoleDashboard($userRole);
    }

    /**
     * Redirect user to their role-specific dashboard
     */
    protected function redirectToRoleDashboard(string $role): Response
    {
        return abort(403, 'Unauthorized');
    }
}
