<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::withCount('orders')->latest();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function export(Request $request)
    {
        $query = User::where('is_admin', false)->latest();
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%");
            });
        }

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Nom', 'Email', 'WhatsApp', 'Nombre de commandes', 'Inscrit le'], ';');
            $query->withCount('orders')->chunkById(500, function ($users) use ($handle) {
                foreach ($users as $user) {
                    fputcsv($handle, [
                        $user->name,
                        $user->email,
                        $user->whatsapp,
                        $user->orders_count,
                        $user->created_at?->format('Y-m-d H:i:s'),
                    ], ';');
                }
            });
            fclose($handle);
        }, 'clients-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function show(User $user)
    {
        $user->load(['orders' => fn ($query) => $query->latest()]);

        return view('admin.users.show', compact('user'));
    }

    public function toggleStatus(User $user)
    {
        abort_if($user->is_admin && $user->id === Auth::id(), 422, 'Vous ne pouvez pas désactiver votre propre compte.');

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active ? 'Client réactivé.' : 'Client désactivé.');
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'admin_role' => 'required|in:super_admin,products,orders,client',
        ]);

        $user->is_admin = $data['admin_role'] !== 'client';
        $user->admin_role = $data['admin_role'] === 'client' ? null : $data['admin_role'];
        $user->save();

        return back()->with('success', 'Rôle mis à jour.');
    }
}