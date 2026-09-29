<?php

namespace App\Http\Middleware;

use App\Models\AdminActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) && $request->user()) {
            $subject = collect($request->route()?->parameters() ?? [])
                ->first(fn ($value) => is_object($value) && method_exists($value, 'getKey'));

            AdminActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => $request->route()?->getName() ?: $request->method(),
                'route' => $request->path(),
                'method' => $request->method(),
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id' => $subject?->getKey(),
                'description' => 'Action administrative exécutée',
                'ip_address' => $request->ip(),
            ]);
        }

        return $response;
    }
}