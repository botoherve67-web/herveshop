<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function edit()
    {
        return view('admin.maintenance.index', [
            'enabled' => AppSetting::read('maintenance_enabled', '0') === '1',
            'message' => AppSetting::read('maintenance_message', 'HerveShop revient très bientôt. Nous effectuons une mise à jour du site.'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'maintenance_message' => 'required|string|max:500',
        ]);

        AppSetting::write('maintenance_enabled', $request->boolean('maintenance_enabled') ? '1' : '0');
        AppSetting::write('maintenance_message', $data['maintenance_message']);

        return back()->with('success', $request->boolean('maintenance_enabled')
            ? 'Mode maintenance activé.'
            : 'Mode maintenance désactivé.');
    }
}