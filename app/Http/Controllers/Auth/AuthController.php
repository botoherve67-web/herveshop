<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifiant' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($request->identifiant, FILTER_VALIDATE_EMAIL) ? 'email' : 'whatsapp';

        if (Auth::attempt([$field => $request->identifiant, 'password' => $request->password], $request->boolean('remember'))) {
            if (! Auth::user()->is_active) {
                Auth::logout();

                return back()->withErrors(['identifiant' => 'Ce compte a ete desactive. Contactez notre equipe.'])->onlyInput('identifiant');
            }

            $request->session()->regenerate();

            return redirect()->intended(Auth::user()->is_admin ? route('admin.dashboard') : route('account.dashboard'));
        }

        return back()->withErrors(['identifiant' => 'Identifiants incorrects.'])->onlyInput('identifiant');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'whatsapp' => 'nullable|string|max:30|unique:users,whatsapp',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        Auth::login($user);
        return redirect()->route('account.dashboard')->with('success', 'Compte cree.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
