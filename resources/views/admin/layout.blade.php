@extends('layouts.app')

@section('content')
    <div class="admin-shell" style="display:flex; gap:24px; flex-wrap:wrap;">
        <aside class="admin-sidebar" style="width:200px;">
            <div class="card">
                <a href="{{ route('admin.dashboard') }}" style="display:block; margin-bottom:10px;"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" style="vertical-align:-4px; margin-right:6px;"><path fill="currentColor" d="M4 19V5h2v14H4Zm7 0V9h2v10h-2Zm7 0V3h2v16h-2Z"/></svg>Tableau de bord</a>
                @if(Auth::user()->hasAdminRole('products'))
                    <a href="{{ route('admin.products.index') }}" style="display:block; margin-bottom:10px;"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" style="vertical-align:-4px; margin-right:6px;"><path fill="currentColor" d="m12 3 8 4v10l-8 4-8-4V7l8-4Zm0 2.2L6.4 8 12 10.8 17.6 8 12 5.2ZM6 9.6v6.2l5 2.5v-6.2L6 9.6Zm7 8.7 5-2.5V9.6l-5 2.5v6.2Z"/></svg>Produits</a>
                    <a href="{{ route('admin.categories.index') }}" style="display:block; margin-bottom:10px;"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" style="vertical-align:-4px; margin-right:6px;"><path fill="currentColor" d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z"/></svg>Catégories</a>
                    <a href="{{ route('admin.products.index', ['stock_faible' => 1]) }}" style="display:block; margin-bottom:10px;">Stocks faibles</a>
                @endif
                @if(Auth::user()->hasAdminRole('orders'))
                    <a href="{{ route('admin.orders.index') }}" style="display:block; margin-bottom:10px;"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" style="vertical-align:-4px; margin-right:6px;"><path fill="currentColor" d="M6 2h9l3 3v17H6V2Zm2 2v16h8V6h-2V4H8Zm2 5h4v2h-4V9Zm0 4h4v2h-4v-2Z"/></svg>Commandes</a>
                    <a href="{{ route('admin.orders.index', ['statut_paiement' => 'en_attente']) }}" style="display:block; margin-bottom:10px;">Paiements en attente</a>
                @endif
                @if(Auth::user()->hasAdminRole('super_admin'))
                    <a href="{{ route('admin.users.index') }}" style="display:block; margin-bottom:10px;"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" style="vertical-align:-4px; margin-right:6px;"><path fill="currentColor" d="M16 11a4 4 0 1 0-3.9-5A4 4 0 0 0 16 11Zm-8 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8 2c-2.7 0-5 1.3-5 3v2h10v-2c0-1.7-2.3-3-5-3ZM8 13c-2.2 0-4 1.1-4 2.5V18h5v-2c0-.9.4-1.7 1.1-2.3A6 6 0 0 0 8 13Z"/></svg>Clients</a>
                    <a href="{{ route('admin.promo-codes.index') }}" style="display:block;"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" style="vertical-align:-4px; margin-right:6px;"><path fill="currentColor" d="m20.6 13.4-7.2 7.2a2 2 0 0 1-2.8 0L3.4 13.4a2 2 0 0 1 0-2.8l7.2-7.2a2 2 0 0 1 1.4-.6H19a2 2 0 0 1 2 2v7a2 2 0 0 1-.4 1.6ZM17 7a1.5 1.5 0 1 0 0 .1V7Z"/></svg>Codes promo</a>
                    <a href="{{ route('admin.reviews.index') }}" style="display:block; margin-top:10px;"><svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" style="vertical-align:-4px; margin-right:6px;"><path fill="currentColor" d="M4 4h16v13H8l-4 4V4Zm3 4v2h10V8H7Zm0 4v2h7v-2H7Z"/></svg>Avis clients</a>
                    <a href="{{ route('admin.activity.index') }}" style="display:block; margin-top:10px;">Historique des actions</a>
                    <a href="{{ route('admin.errors.index') }}" style="display:block; margin-top:10px;">Journal des erreurs</a>
                    <a href="{{ route('admin.maintenance.edit') }}" style="display:block; margin-top:10px;">Mode maintenance</a>
                @endif
            </div>
        </aside>
        <div class="admin-content" style="flex:1; min-width:280px;">
            @yield('admin-content')
        </div>
    </div>
@endsection
