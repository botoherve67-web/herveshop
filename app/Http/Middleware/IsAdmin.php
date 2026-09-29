<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        $allowed = empty($roles) || collect($roles)->contains(fn (string $role) => $user?->hasAdminRole($role));

        abort_unless($allowed, 403);

        return $next($request);
    }
}
