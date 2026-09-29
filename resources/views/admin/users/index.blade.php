@extends('admin.layout')

@section('title', 'Clients — Admin')

@section('admin-content')
    <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
        <h1>Clients inscrits</h1>
        <a href="{{ route('admin.users.export', request()->only('q')) }}" class="btn">Exporter CSV</a>
        <form method="GET" style="display:flex; gap:8px; min-width:280px;">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Nom, email ou WhatsApp">
            <button type="submit" class="btn">Rechercher</button>
        </form>
    </div>

    @forelse($users as $user)
        <div class="card" style="display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:8px; flex-wrap:wrap;">
            <div>
                <a href="{{ route('admin.users.show', $user) }}"><strong>{{ $user->name }}</strong></a>
                <span class="{{ $user->is_active ? 'badge-precommande' : 'alert-error' }}" style="padding:2px 6px;">{{ $user->is_active ? 'Actif' : 'Désactivé' }}</span>
                @if($user->is_admin)
                    <span class="badge-precommande">Administrateur</span>
                @endif
                <br>
                {{ $user->email }} @if($user->whatsapp) — {{ $user->whatsapp }} @endif
            </div>
            <div style="text-align:right;">
                <strong>{{ $user->orders_count }}</strong> commande{{ $user->orders_count > 1 ? 's' : '' }}<br>
                <small>Inscrit le {{ $user->created_at->format('d/m/Y') }}</small>
            </div>
            <form action="{{ route('admin.users.role', $user) }}" method="POST" style="min-width:210px;">
                @csrf
                @method('PATCH')
                <label>Rôle</label>
                <select name="admin_role" onchange="this.form.submit()">
                    <option value="client" @selected(!$user->is_admin)>Client</option>
                    <option value="super_admin" @selected($user->is_admin && ($user->admin_role ?? 'super_admin') === 'super_admin')>Super-admin</option>
                    <option value="products" @selected($user->admin_role === 'products')>Gestionnaire produits</option>
                    <option value="orders" @selected($user->admin_role === 'orders')>Gestionnaire commandes</option>
                </select>
            </form>
            <form action="{{ route('admin.users.status', $user) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn outline">{{ $user->is_active ? 'Désactiver' : 'Réactiver' }}</button>
            </form>
        </div>
    @empty
        <div class="card">Aucun client trouvé.</div>
    @endforelse

    {{ $users->links() }}
@endsection
