@extends('admin.layout')

@section('title', 'Client '.$user->name.' — Admin')

@section('admin-content')
    <p><a href="{{ route('admin.users.index') }}">← Retour aux clients</a></p>
    <h1>{{ $user->name }}</h1>
    <div class="card">
        <p><strong>E-mail :</strong> {{ $user->email }}</p>
        <p><strong>WhatsApp :</strong> {{ $user->whatsapp ?: 'Non renseigné' }}</p>
        <p><strong>Statut :</strong> {{ $user->is_active ? 'Actif' : 'Désactivé' }}</p>
        <p><strong>Adresse :</strong> {{ $user->address ?: 'Non renseignée' }}{{ $user->zone ? ' — '.$user->zone : '' }}</p>
        <form action="{{ route('admin.users.status', $user) }}" method="POST">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn">{{ $user->is_active ? 'Désactiver ce client' : 'Réactiver ce client' }}</button>
        </form>
    </div>

    <h2>Historique des commandes</h2>
    @forelse($user->orders as $order)
        <a href="{{ route('admin.orders.show', $order) }}" class="card" style="display:block; margin-bottom:8px;">
            <strong>{{ $order->reference }}</strong> — {{ number_format($order->total, 0, ',', ' ') }} FCFA
            — {{ ucfirst(str_replace('_', ' ', $order->statut)) }}
            — Paiement : {{ ucfirst(str_replace('_', ' ', $order->statut_paiement)) }}
        </a>
    @empty
        <div class="card">Aucune commande pour ce client.</div>
    @endforelse
@endsection