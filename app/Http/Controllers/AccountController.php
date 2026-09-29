<?php

namespace App\Http\Controllers;

use App\Notifications\ProfileUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $orders = $user->orders()->with('items.product.images')->latest()->take(5)->get();
        $ordersCount = $user->orders()->count();
        $reviewsCount = $user->reviews()->count();
        $wishlistCount = $user->wishlistItems()->count();

        return view('account.dashboard', compact('user', 'orders', 'ordersCount', 'reviewsCount', 'wishlistCount'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'zone' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|string|in:homme,femme,autre',
            'delivery_notes' => 'nullable|string|max:255',
        ]);

        $user->update($request->only('name', 'whatsapp', 'address', 'zone', 'birth_date', 'gender', 'delivery_notes'));
        $user->notify(new ProfileUpdatedNotification);

        return back()->with('success', 'Profil mis à jour.');
    }
}
