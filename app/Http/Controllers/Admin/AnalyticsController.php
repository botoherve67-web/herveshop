<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function click(Request $request)
    {
        $data = $request->validate(['target' => 'required|string|max:500']);
        AnalyticsEvent::create([
            'type' => 'click',
            'path' => '/'.ltrim($request->path(), '/'),
            'target' => $data['target'],
            'visitor_hash' => hash('sha256', $request->ip().'|'.$request->userAgent().'|'.now()->toDateString()),
            'user_id' => $request->user()?->id,
            'ip_hash' => hash('sha256', (string) $request->ip()),
        ]);

        return response()->noContent();
    }

    public function index(Request $request)
    {
        $from = now()->subDays((int) $request->integer('days', 30));
        $events = AnalyticsEvent::where('created_at', '>=', $from);

        $stats = [
            'visites' => (clone $events)->where('type', 'page_view')->count(),
            'visiteurs' => (clone $events)->where('type', 'page_view')->distinct('visitor_hash')->count('visitor_hash'),
            'clics' => (clone $events)->where('type', 'click')->count(),
        ];
        $pages = (clone $events)->where('type', 'page_view')
            ->selectRaw('path, COUNT(*) as total')
            ->groupBy('path')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('admin.analytics.index', compact('stats', 'pages'));
    }
}
