<?php

namespace App\Http\Middleware;

use App\Models\AnalyticsEvent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAnalytics
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && ! $request->is('admin*') && ! $request->is('up')) {
            $visitor = hash('sha256', $request->ip().'|'.$request->userAgent().'|'.now()->toDateString());

            AnalyticsEvent::create([
                'type' => 'page_view',
                'path' => '/'.ltrim($request->path(), '/'),
                'visitor_hash' => $visitor,
                'user_id' => $request->user()?->id,
                'ip_hash' => hash('sha256', (string) $request->ip()),
            ]);
        }

        return $response;
    }
}
