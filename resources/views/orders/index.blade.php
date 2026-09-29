@extends('layouts.app')

@section('title', 'Mes commandes')

@section('content')
    <style>
        .orders-head{display:flex;align-items:end;justify-content:space-between;gap:16px;margin-bottom:18px}.orders-head h1{margin:0}.orders-head p{margin:4px 0 0;color:#7187a4;font-size:.72rem}.orders-list{display:grid;gap:12px}.order-card{display:grid;grid-template-columns:70px minmax(0,1fr) 180px 32px;gap:14px;align-items:center;padding:14px;border:1px solid #e3edf8;border-radius:8px;background:#fff;box-shadow:0 8px 24px rgba(28,78,128,.06);color:inherit}.order-art{display:grid;place-items:center;width:70px;height:70px;overflow:hidden;border-radius:8px;background:#edf5ff;color:#0969ed}.order-art img{width:100%;height:100%;object-fit:contain}.order-title{min-width:0}.order-title strong{display:block;color:#173f5f;font-size:.82rem}.order-title small{display:block;margin-top:3px;color:#7187a4;font-size:.62rem}.order-meta{display:flex;gap:8px;flex-wrap:wrap;margin-top:9px}.order-pill{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:#eaf4ff;color:#0969ed;font-size:.58rem;font-weight:700}.order-pill.pay{background:#fff8e9;color:#a06400}.order-pill.done{background:#e6f8ee;color:#15965c}.order-summary{text-align:right}.order-summary strong{display:block;color:#173f5f;font-size:.8rem}.order-summary span{display:block;margin-top:4px;color:#7187a4;font-size:.6rem}.order-arrow{font-size:1.4rem;color:#0969ed}.orders-empty{padding:22px;border:1px solid #e3edf8;border-radius:8px;background:#fff;color:#7187a4}@media(max-width:700px){.orders-head{display:block}.order-card{grid-template-columns:56px minmax(0,1fr) 22px}.order-art{width:56px;height:56px}.order-summary{grid-column:2;text-align:left}.order-arrow{grid-column:3;grid-row:1}}
    </style>

    <div class="orders-head">
        <div>
            <h1>Mes commandes</h1>
            <p>Suivez vos achats, paiements, livraisons et documents.</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn outline">Continuer mes achats</a>
    </div>

    <div class="orders-list">
        @forelse($orders as $order)
            @php
                $item = $order->items->first();
                $image = $item?->product?->images?->first();
                $done = in_array($order->statut, ['livree'], true);
            @endphp
            <a href="{{ route('orders.show', $order) }}" class="order-card">
                <span class="order-art">
                    @if($image)
                        <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $item?->product_name }}">
                    @else
                        <x-icon name="box" size="24"/>
                    @endif
                </span>
                <span class="order-title">
                    <strong>{{ $order->reference }}</strong>
                    <small>{{ $item?->product_name ?: 'Commande HerveShop' }} @if($order->items->count() > 1)+ {{ $order->items->count() - 1 }} article(s)@endif</small>
                    <span class="order-meta">
                        <span class="order-pill {{ $done ? 'done' : '' }}">{{ ucfirst(str_replace('_', ' ', $order->statut)) }}</span>
                        <span class="order-pill pay">{{ ucfirst(str_replace('_', ' ', $order->statut_paiement)) }}</span>
                        @if($order->tracking_code)<span class="order-pill">Suivi {{ $order->tracking_code }}</span>@endif
                    </span>
                </span>
                <span class="order-summary">
                    <strong>{{ number_format($order->total, 0, ',', ' ') }} FCFA</strong>
                    <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </span>
                <span class="order-arrow">›</span>
            </a>
        @empty
            <div class="orders-empty">
                Aucune commande pour l'instant.
            </div>
        @endforelse
    </div>

    <div style="margin-top:18px;">
        {{ $orders->links() }}
    </div>
@endsection
