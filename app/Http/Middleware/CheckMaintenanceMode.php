<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isExempt($request) || ! Schema::hasTable('app_settings') || AppSetting::read('maintenance_enabled', '0') !== '1') {
            return $next($request);
        }

        return response()->view('maintenance', [
            'message' => AppSetting::read('maintenance_message', 'HerveShop revient très bientôt. Nous effectuons une mise à jour du site.'),
        ], 503);
    }

    protected function isExempt(Request $request): bool
    {
        return $request->is('admin')
            || $request->is('admin/*')
            || $request->is('connexion*')
            || $request->is('inscription*')
            || $request->is('verification-email*')
            || $request->is('up');
    }
}