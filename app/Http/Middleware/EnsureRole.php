<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Admins pass right through unconditionally
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Map allowed string args to true enum states cleanly
        $allowed = array_map(
            fn (string $role) => UserRole::tryFrom($role)?->value ?? $role,
            $roles
        );

        // Standard strict-type check against the role payload
        if (in_array($user->role, $allowed, true)) {
            return $next($request);
        }

        return redirect()->route('dashboard');
    }
}